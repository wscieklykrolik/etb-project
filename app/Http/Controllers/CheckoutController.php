<?php

namespace App\Http\Controllers;

use App\Contracts\PaymentGatewayInterface;
use App\Contracts\ShippingProviderInterface;
use App\Models\Cart;
use App\Models\Order;
use App\Services\CartService;
use App\Services\InvoiceService;
use App\Services\OrderNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CartService $cart,
        private readonly PaymentGatewayInterface $gateway,
        private readonly ShippingProviderInterface $shipping,
        private readonly OrderNotificationService $notifications,
        private readonly InvoiceService $invoices,
    ) {}

    public function shipping(): View|RedirectResponse
    {
        if ($this->cart->getItems(auth()->user())->isEmpty()) {
            return redirect()->route('shop.index')->with('error', __('Koszyk jest pusty.'));
        }

        $items = $this->cart->getItems(auth()->user());
        $totalGrosze = $this->cart->totalGrosze(auth()->user());
        $shippingGrosze = $this->calculateShipping($items);
        $needsShipping = $this->requiresShipping($items);

        return view('checkout.shipping', compact('items', 'totalGrosze', 'shippingGrosze', 'needsShipping'));
    }

    public function storeShipping(Request $request): RedirectResponse
    {
        $items = $this->cart->getItems(auth()->user());
        $needsShipping = $this->requiresShipping($items);

        $rules = [];
        if ($needsShipping) {
            $rules = [
                'shipping_method' => ['required', 'string', 'in:inpost_locker,inpost_courier,dpd_locker,dpd_courier,pickup'],
                'address.recipient_name' => ['required', 'string', 'max:255'],
                'address.street' => ['nullable', 'required_if:shipping_method,inpost_courier,dpd_courier', 'string', 'max:255'],
                'address.city' => ['nullable', 'required_if:shipping_method,inpost_courier,dpd_courier', 'string', 'max:255'],
                'address.postal_code' => ['nullable', 'required_if:shipping_method,inpost_courier,dpd_courier', 'string', 'max:20'],
                'address.country' => ['nullable', 'required_if:shipping_method,inpost_courier,dpd_courier', 'string', 'max:100'],
                'address.locker_name' => ['nullable', 'required_if:shipping_method,inpost_locker,dpd_locker', 'string', 'max:100'],
                'address.locker_address' => ['nullable', 'required_if:shipping_method,inpost_locker,dpd_locker', 'string', 'max:255'],
                'address.locker_city' => ['nullable', 'required_if:shipping_method,inpost_locker,dpd_locker', 'string', 'max:255'],
            ];
        }

        $validated = $request->validate($rules);

        $cart = Cart::firstOrCreate(['user_id' => auth()->id()]);
        $cart->shipping_method = $needsShipping ? $validated['shipping_method'] : null;
        $cart->shipping_address = $needsShipping ? $validated['address'] : null;
        $cart->save();

        return redirect()->route('checkout.payment');
    }

    public function payment(): View|RedirectResponse
    {
        if ($this->cart->getItems(auth()->user())->isEmpty()) {
            return redirect()->route('shop.index')->with('error', __('Koszyk jest pusty.'));
        }

        $items = $this->cart->getItems(auth()->user());
        $totalGrosze = $this->cart->totalGrosze(auth()->user());
        $cart = Cart::where('user_id', auth()->id())->first();
        $shippingGrosze = $cart ? $this->calculateShipping($items) : 0;

        return view('checkout.payment', compact('items', 'totalGrosze', 'shippingGrosze', 'cart'));
    }

    public function place(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $items = $this->cart->getItems($user);

        if ($items->isEmpty()) {
            return redirect()->route('shop.index')->with('error', __('Koszyk jest pusty.'));
        }

        $cart = Cart::where('user_id', $user->id)->first();
        $shippingGrosze = $this->calculateShipping($items);
        $address = $cart?->shipping_address ?? [];
        $address['shipping_method'] = $cart?->shipping_method;
        $totalNetGrosze = 0;
        $totalVatGrosze = 0;

        $orderItemsData = [];
        foreach ($items as $item) {
            $product = $item->product;
            $vatRate = $product->vat_rate ?? 23;
            $netPrice = $item->unit_price_grosze;
            $vatAmount = (int) round($netPrice * $vatRate / 100);
            $grossPrice = $netPrice + $vatAmount;
            $totalNetGrosze += $netPrice * $item->qty;
            $totalVatGrosze += $vatAmount * $item->qty;

            $orderItemsData[] = [
                'product_id' => $product->id,
                'variant_size_id' => $item->variant?->id,
                'qty' => $item->qty,
                'unit_price_grosze' => $item->unit_price_grosze,
                'vat_rate' => $vatRate,
                'net_price_grosze' => $netPrice,
                'gross_price_grosze' => $grossPrice,
            ];
        }

        $totalGrosze = $totalNetGrosze + $totalVatGrosze;

        $idempotencyKey = (string) str()->uuid();

        try {
            $order = DB::transaction(function () use ($user, $cart, $totalGrosze, $shippingGrosze, $idempotencyKey, $totalNetGrosze, $totalVatGrosze, $orderItemsData) {
                $order = Order::create([
                    'user_id' => $user->id,
                    'status' => Order::STATUS_PENDING_PAYMENT,
                    'total_grosze' => $totalGrosze,
                    'total_net_grosze' => $totalNetGrosze,
                    'total_vat_grosze' => $totalVatGrosze,
                    'shipping_grosze' => $shippingGrosze,
                    'shipping_method' => $cart?->shipping_method,
                    'shipping_address' => $cart?->shipping_address,
                    'idempotency_key' => $idempotencyKey,
                ]);

                foreach ($orderItemsData as $itemData) {
                    $order->items()->create($itemData);
                }

                return $order;
            });

            $token = $this->gateway->createPayment($order);

            $this->cart->clear($user);

            $paymentUrl = config('przelewy24.base_url', 'https://sandbox.przelewy24.pl').
                '/trnRequest/'.$token;

            return redirect()->away($paymentUrl);
        } catch (\Exception $e) {
            Log::error('Order placement failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);

            return redirect()->route('checkout.payment')
                ->with('error', __('Nie udało się złożyć zamówienia. Spróbuj ponownie.'));
        }
    }

    public function webhook(Request $request): JsonResponse
    {
        $data = $request->all();

        try {
            $verified = $this->gateway->verifyTransaction($data);

            if (! $verified) {
                Log::warning('P24 webhook: invalid signature', $data);

                return response()->json(['status' => 'error'], 400);
            }

            $sessionId = $data['sessionId'];
            $orderId = (int) explode('|', $sessionId)[0];
            $order = Order::findOrFail($orderId);

            if ($order->isPaid()) {
                return response()->json(['status' => 'already_paid']);
            }

            DB::transaction(function () use ($order) {
                $order->update([
                    'status' => Order::STATUS_PAID,
                    'paid_at' => now(),
                ]);

                if (! $order->hasInvoice()) {
                    $this->invoices->generate($order);
                }
            });

            $this->notifications->sendConfirmation($order);

            Log::info("Order #{$order->id} paid via P24");

            return response()->json(['status' => 'ok']);
        } catch (\Exception $e) {
            Log::error('P24 webhook error', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);

            return response()->json(['status' => 'error'], 500);
        }
    }

    public function confirmation(Order $order): View|RedirectResponse
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $order->load('items.product', 'items.variantSize');

        return view('checkout.confirmation', compact('order'));
    }

    private function requiresShipping($items): bool
    {
        return $items->contains(fn ($item) => $item->product?->is_physical);
    }

    private function calculateShipping($items): int
    {
        if (! $this->requiresShipping($items)) {
            return 0;
        }

        $cart = Cart::where('user_id', auth()->id())->first();
        $method = $cart?->shipping_method;

        $methods = config('shipping.methods', []);

        if ($method && isset($methods[$method])) {
            return $methods[$method]['price_grosze'];
        }

        if (! empty($methods)) {
            return collect($methods)->min('price_grosze');
        }

        return 0;
    }
}

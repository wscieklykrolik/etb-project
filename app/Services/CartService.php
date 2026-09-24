<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariantSize;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CartService
{
    private const GUEST_CART_KEY = 'guest_cart';

    public function getOrCreateCart(?User $user): Cart
    {
        if ($user) {
            return Cart::firstOrCreate(['user_id' => $user->id]);
        }

        return $this->guestCart();
    }

    public function addItem(?User $user, int $productId, ?int $variantSizeId, int $qty): void
    {
        $product = Product::findOrFail($productId);
        $priceGrosze = $this->resolvePrice($product, $variantSizeId);

        if ($user) {
            $cart = $this->getOrCreateCart($user);
            $cartItem = $cart->items()
                ->where('product_id', $productId)
                ->where('variant_size_id', $variantSizeId)
                ->first();

            if ($cartItem) {
                $cartItem->increment('qty', $qty);
            } else {
                $cart->items()->create([
                    'product_id' => $productId,
                    'variant_size_id' => $variantSizeId,
                    'qty' => $qty,
                    'unit_price_grosze' => $priceGrosze,
                ]);
            }

            $cart->load('items');
            $cart->recalculateTotal();
        } else {
            $items = $this->getGuestCartItems();
            $key = "{$productId}_{$variantSizeId}";

            if (isset($items[$key])) {
                $items[$key]['qty'] += $qty;
            } else {
                $items[$key] = [
                    'product_id' => $productId,
                    'variant_size_id' => $variantSizeId,
                    'qty' => $qty,
                    'unit_price_grosze' => $priceGrosze,
                ];
            }

            session([self::GUEST_CART_KEY => $items]);
        }
    }

    public function removeItem(?User $user, int $productId, ?int $variantSizeId): void
    {
        if ($user) {
            $cart = $this->getOrCreateCart($user);
            $cart->items()
                ->where('product_id', $productId)
                ->where('variant_size_id', $variantSizeId)
                ->delete();
            $cart->load('items');
            $cart->recalculateTotal();
        } else {
            $items = $this->getGuestCartItems();
            $key = "{$productId}_{$variantSizeId}";
            unset($items[$key]);
            session([self::GUEST_CART_KEY => $items]);
        }
    }

    public function updateQty(?User $user, int $productId, ?int $variantSizeId, int $qty): void
    {
        if ($qty < 1) {
            $this->removeItem($user, $productId, $variantSizeId);

            return;
        }

        if ($user) {
            $cart = $this->getOrCreateCart($user);
            $cart->items()
                ->where('product_id', $productId)
                ->where('variant_size_id', $variantSizeId)
                ->update(['qty' => $qty]);
            $cart->load('items');
            $cart->recalculateTotal();
        } else {
            $items = $this->getGuestCartItems();
            $key = "{$productId}_{$variantSizeId}";
            if (isset($items[$key])) {
                $items[$key]['qty'] = $qty;
            }
            session([self::GUEST_CART_KEY => $items]);
        }
    }

    public function getItemCount(?User $user): int
    {
        if ($user) {
            return Cart::where('user_id', $user->id)
                ->withSum('items as total_qty', 'qty')
                ->value('total_qty') ?? 0;
        }

        return collect($this->getGuestCartItems())->sum('qty');
    }

    public function getItems(?User $user): Collection
    {
        if ($user) {
            $cart = $this->getOrCreateCart($user);
            $cart->load('items.product', 'items.variantSize');

            return $cart->items->map(function (CartItem $item) {
                return (object) [
                    'product' => $item->product,
                    'variant' => $item->variantSize,
                    'variant_size_id' => $item->variant_size_id,
                    'qty' => $item->qty,
                    'unit_price_grosze' => $item->unit_price_grosze,
                    'subtotal_grosze' => $item->subtotal(),
                ];
            });
        }

        return collect($this->getGuestCartItems())->map(function (array $data) {
            $product = Product::with('variantSizes')->find($data['product_id']);
            $variant = $data['variant_size_id'] ? ProductVariantSize::find($data['variant_size_id']) : null;

            return (object) [
                'product' => $product,
                'variant' => $variant,
                'variant_size_id' => $data['variant_size_id'],
                'qty' => $data['qty'],
                'unit_price_grosze' => $data['unit_price_grosze'],
                'subtotal_grosze' => $data['qty'] * $data['unit_price_grosze'],
            ];
        })->filter(fn ($item) => $item->product !== null);
    }

    public function totalGrosze(?User $user): int
    {
        return $this->getItems($user)->sum('subtotal_grosze');
    }

    public function clear(?User $user): void
    {
        if ($user) {
            Cart::where('user_id', $user->id)->delete();
        } else {
            session()->forget(self::GUEST_CART_KEY);
        }
    }

    public function mergeGuestIntoUser(User $user): void
    {
        $guestItems = $this->getGuestCartItems();
        if (empty($guestItems)) {
            return;
        }

        DB::transaction(function () use ($user, $guestItems) {
            $cart = Cart::firstOrCreate(['user_id' => $user->id]);

            foreach ($guestItems as $item) {
                $existing = $cart->items()
                    ->where('product_id', $item['product_id'])
                    ->where('variant_size_id', $item['variant_size_id'])
                    ->first();

                if ($existing) {
                    $existing->increment('qty', $item['qty']);
                } else {
                    $cart->items()->create($item);
                }
            }

            $cart->load('items');
            $cart->recalculateTotal();
        });

        session()->forget(self::GUEST_CART_KEY);
    }

    private function getGuestCartItems(): array
    {
        return session(self::GUEST_CART_KEY, []);
    }

    private function guestCart(): Cart
    {
        $items = $this->getGuestCartItems();
        $cart = new Cart;
        $cart->total_grosze = collect($items)->sum(fn ($i) => $i['qty'] * $i['unit_price_grosze']);

        return $cart;
    }

    private function resolvePrice(Product $product, ?int $variantSizeId): int
    {
        if ($variantSizeId) {
            $variant = ProductVariantSize::find($variantSizeId);

            return $product->price_grosze + ($variant?->extra_price_grosze ?? 0);
        }

        return $product->price_grosze;
    }
}

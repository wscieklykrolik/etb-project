<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(private readonly CartService $cart) {}

    public function index(): View
    {
        $items = $this->cart->getItems(auth()->user());
        $totalGrosze = $this->cart->totalGrosze(auth()->user());

        return view('cart.index', compact('items', 'totalGrosze'));
    }

    public function add(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'variant_size_id' => ['nullable', 'exists:product_variant_sizes,id'],
            'qty' => ['integer', 'min:1', 'max:99'],
        ]);

        $this->cart->addItem(
            auth()->user(),
            (int) $validated['product_id'],
            isset($validated['variant_size_id']) ? (int) $validated['variant_size_id'] : null,
            (int) ($validated['qty'] ?? 1),
        );

        return redirect()->back()->with('success', __('Dodano do koszyka.'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'items' => ['required', 'array'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.variant_size_id' => ['nullable', 'exists:product_variant_sizes,id'],
            'items.*.qty' => ['required', 'integer', 'min:0', 'max:99'],
        ]);

        foreach ($validated['items'] as $item) {
            $this->cart->updateQty(
                auth()->user(),
                (int) $item['product_id'],
                isset($item['variant_size_id']) ? (int) $item['variant_size_id'] : null,
                (int) $item['qty'],
            );
        }

        return redirect()->route($request->boolean('order') ? 'message-order.show' : 'cart.index')->with('success', __('Koszyk zaktualizowany.'));
    }

    public function remove(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'variant_size_id' => ['nullable', 'exists:product_variant_sizes,id'],
        ]);

        $this->cart->removeItem(
            auth()->user(),
            (int) $validated['product_id'],
            isset($validated['variant_size_id']) ? (int) $validated['variant_size_id'] : null,
        );

        return redirect()->route('cart.index')->with('success', __('Usunięto z koszyka.'));
    }

    public function badge(Request $request): JsonResponse
    {
        return response()->json([
            'count' => $this->cart->getItemCount(auth()->user()),
        ]);
    }
}

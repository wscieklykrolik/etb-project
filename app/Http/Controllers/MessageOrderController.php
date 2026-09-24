<?php

namespace App\Http\Controllers;

use App\Services\InPostPublicPriceService;
use App\Services\MessageOrderService;
use App\Support\ShopSettings;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MessageOrderController extends Controller
{
    public function __invoke(Request $request, MessageOrderService $orders, InPostPublicPriceService $prices)
    {
        if (ShopSettings::legacy()) {
            return redirect()->route('checkout.shipping');
        }
        try {
            $items = $orders->items($request->user());
        } catch (ValidationException $exception) {
            return redirect()->route('cart.index')->withErrors($exception->errors());
        }
        if ($items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Twój koszyk jest pusty.');
        }
        $quote = $prices->quote();
        $delivery = null;
        $text = null;
        if ($request->isMethod('POST')) {
            $delivery = $request->validate(['delivery' => ['required', Rule::in(['inpost', 'pickup'])]])['delivery'];
            if ($delivery === 'inpost' && $quote['price_grosze'] === null) {
                throw ValidationException::withMessages(['delivery' => 'Nie możemy teraz potwierdzić ceny InPost. Spróbuj ponownie później lub wybierz bezpłatny odbiór na meczu.']);
            }
            $text = $orders->text($items, $delivery, $delivery === 'pickup' ? 0 : $quote['price_grosze']);
        }

        return response()->view('checkout.message', [
            'quote' => $quote,
            'items' => $items,
            'totalGrosze' => $items->sum('subtotal_grosze'),
            'contact' => ShopSettings::contact(),
            'delivery' => $delivery,
            'orderText' => $text,
        ])->header('Cache-Control', 'no-store, private');
    }
}

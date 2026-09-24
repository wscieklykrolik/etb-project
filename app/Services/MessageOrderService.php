<?php

namespace App\Services;

use App\Models\User;
use App\Support\ShopSettings;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class MessageOrderService
{
    public function __construct(private readonly CartService $cart) {}

    public function items(?User $user): Collection
    {
        return $this->cart->getItems($user)->map(function ($item) {
            $product = $item->product;
            $variant = $item->variant;
            if (! $product || ! $product->is_published || $item->qty < 1 || $item->qty > 99
                || ($item->variant_size_id && (! $variant || $variant->product_id !== $product->id))
                || (! $variant && $product->variantSizes()->exists())) {
                throw ValidationException::withMessages([
                    'cart' => 'Sprawdź koszyk: produkt lub rozmiar jest niedostępny albo ilość jest nieprawidłowa. Usuń tę pozycję i wybierz ją ponownie w sklepie.',
                ]);
            }

            $price = $product->price_grosze + ($variant?->extra_price_grosze ?? 0);

            return (object) [...(array) $item, 'unit_price_grosze' => $price, 'subtotal_grosze' => $price * $item->qty];
        })->values();
    }

    public function text(Collection $items, string $delivery, int $shippingGrosze): string
    {
        $contact = ShopSettings::contact();
        $lines = ['Dzień dobry! Chcę zamówić w Eat The Ball:', ''];
        foreach ($items as $item) {
            $name = preg_replace('/[\r\n]+/u', ' ', $item->product->name);
            $size = $item->variant ? ' | Rozmiar: '.preg_replace('/[\r\n]+/u', ' ', $item->variant->size_label) : '';
            $lines[] = 'ID produktu: '.$item->product->id.' | '.$name.$size
                .' | Ilość: '.$item->qty.' | Cena za sztukę: '.$this->money($item->unit_price_grosze)
                .' | Suma: '.$this->money($item->subtotal_grosze);
        }
        $lines[] = '';
        $lines[] = 'Suma produktów: '.$this->money($items->sum('subtotal_grosze'));
        $lines[] = 'Dostawa: '.($delivery === 'inpost' ? 'Paczkomat InPost, gabaryt B — '.$this->money($shippingGrosze) : 'Odbiór osobisty na meczu — 0,00 zł.');
        $lines[] = 'Razem z dostawą: '.$this->money($items->sum('subtotal_grosze') + $shippingGrosze);
        $lines[] = $contact[$delivery];
        $lines[] = 'Płatność: '.$contact['payment'];
        $lines[] = '';
        $lines[] = 'Kontakt: Instagram @eat_the_ball — https://ig.me/m/eat_the_ball/';
        if ($contact['email']) {
            $lines[] = 'E-mail: '.$contact['email'];
        }
        $lines[] = 'Proszę o potwierdzenie dostępności, końcowej kwoty i szczegółów realizacji.';

        return implode("\n", $lines);
    }

    private function money(int $grosze): string
    {
        return number_format($grosze / 100, 2, ',', ' ').' zł';
    }
}

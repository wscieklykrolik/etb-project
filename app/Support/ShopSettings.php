<?php

namespace App\Support;

use App\Models\AppSetting;

class ShopSettings
{
    public static function legacy(): bool
    {
        return AppSetting::getValue('shop_order_mode', 'message') === 'legacy';
    }

    public static function contact(): array
    {
        return [
            'email' => AppSetting::getValue('shop_order_email', ''),
            'payment' => AppSetting::getValue('shop_payment_instructions', 'Po wysłaniu zamówienia poczekaj na potwierdzenie dostępności i dane do płatności. Nie wpłacaj pieniędzy przed naszym potwierdzeniem.'),
            'inpost' => AppSetting::getValue('shop_inpost_instructions', 'Wysyłka do Paczkomatu InPost, gabaryt B (do 19 × 38 × 64 cm i 25 kg). Dane Paczkomatu podaj w wiadomości. Jeśli zamówienie nie zmieści się w jednej paczce, ustalimy z Tobą koszt przed płatnością.'),
            'pickup' => AppSetting::getValue('shop_pickup_instructions', 'Odbiór osobisty na meczu — bez opłaty za dostawę. Termin i miejsce odbioru potwierdzimy w wiadomości.'),
        ];
    }
}

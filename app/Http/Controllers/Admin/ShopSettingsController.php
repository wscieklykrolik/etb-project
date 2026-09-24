<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Services\InPostPublicPriceService;
use App\Support\ShopSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ShopSettingsController extends Controller
{
    public function edit(InPostPublicPriceService $prices)
    {
        return view('admin.shop-settings', ['quote' => $prices->quote(), 'contact' => ShopSettings::contact(), 'legacy' => ShopSettings::legacy()]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'email' => ['nullable', 'email', 'max:254'],
            'payment' => ['required', 'string', 'max:3000'],
            'inpost' => ['required', 'string', 'max:1500'],
            'pickup' => ['required', 'string', 'max:1500'],
        ]);
        DB::transaction(function () use ($data) {
            foreach (['email' => 'shop_order_email', 'payment' => 'shop_payment_instructions', 'inpost' => 'shop_inpost_instructions', 'pickup' => 'shop_pickup_instructions'] as $field => $key) {
                AppSetting::setValue($key, $data[$field] ?? '');
            }
        });

        return back()->with('success', 'Zapisano ustawienia zamówień przez wiadomości.');
    }

    public function mode(Request $request)
    {
        $data = $request->validate([
            'mode' => ['required', Rule::in(['message', 'legacy'])],
            'confirmation' => ['required', Rule::in(['ZMIENIAM TRYB SKLEPU'])],
            'acknowledged' => ['accepted'],
        ]);
        AppSetting::setValue('shop_order_mode', $data['mode']);

        return back()->with('success', 'Zmieniono sposób zamawiania dla całej strony.');
    }
}

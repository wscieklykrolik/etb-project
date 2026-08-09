@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-3xl px-6 py-10 text-white">
    <h1 class="mb-6 text-3xl font-black">{{ __('Płatność') }}</h1>

    <div class="mb-6 rounded-lg border border-zinc-300 bg-white p-6 text-zinc-950 shadow">
        <h2 class="mb-4 text-xl font-black">{{ __('Podsumowanie zamówienia') }}</h2>
        @foreach($items as $item)
            <div class="flex justify-between gap-4 border-b border-zinc-200 py-2 text-sm">
                <span>{{ $item->product->name }} × {{ $item->qty }}</span>
                <span class="font-bold">{{ number_format($item->subtotal_grosze / 100, 2, ',', '') }} zł</span>
            </div>
        @endforeach

        @if($cart?->shipping_method)
            @php($shippingLabel = config('shipping.methods.' . $cart->shipping_method . '.label', $cart->shipping_method))
            <div class="flex justify-between gap-4 border-b border-yellow-300 bg-yellow-50 px-3 py-2 text-sm text-zinc-950">
                <span class="font-black">{{ __('Dostawa') }}: {{ $shippingLabel }}</span>
                <span class="font-black">{{ number_format($shippingGrosze / 100, 2, ',', '') }} zł</span>
            </div>
        @endif

        <div class="flex justify-between py-3 text-lg font-black">
            <span>{{ __('Do zapłaty') }}</span>
            <span>{{ number_format(($totalGrosze + $shippingGrosze) / 100, 2, ',', '') }} zł</span>
        </div>
    </div>

    @if($cart?->shipping_address)
        <div class="mb-6 rounded-lg border border-zinc-300 bg-white p-6 text-zinc-950 shadow">
            <h3 class="mb-3 text-lg font-black">{{ __('Dane dostawy') }}</h3>
            <p class="font-bold">{{ $cart->shipping_address['recipient_name'] ?? '' }}</p>
            @if(! empty($cart->shipping_address['locker_name']))
                <div class="mt-3 rounded-lg border border-yellow-300 bg-yellow-50 p-3">
                    <p class="text-xs font-bold uppercase tracking-wide text-yellow-800">Paczkomat</p>
                    <p class="font-black">{{ $cart->shipping_address['locker_name'] }}</p>
                    <p class="text-sm text-zinc-700">{{ $cart->shipping_address['locker_address'] ?? '' }}, {{ $cart->shipping_address['locker_city'] ?? '' }}</p>
                </div>
            @else
                <p class="mt-2">{{ $cart->shipping_address['street'] ?? '' }}</p>
                <p>{{ $cart->shipping_address['postal_code'] ?? '' }} {{ $cart->shipping_address['city'] ?? '' }}</p>
                <p>{{ $cart->shipping_address['country'] ?? '' }}</p>
            @endif
        </div>
    @endif

    <form method="POST" action="{{ route('checkout.place') }}" class="text-center">
        @csrf
        <button type="submit" class="inline-flex items-center gap-3 rounded-lg bg-yellow-400 px-8 py-4 text-lg font-black text-black hover:bg-yellow-300">
            <i data-lucide="credit-card" class="h-6 w-6"></i>
            {{ __('Zapłać z Przelewy24') }}
        </button>
    </form>

    <div class="mt-4 text-center">
        <a href="{{ route('checkout.shipping') }}" class="text-zinc-400 hover:text-yellow-400">← {{ __('Powrót do dostawy') }}</a>
    </div>
</div>
@endsection

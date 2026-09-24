@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-6 py-8">
    <div class="bg-green-50 border border-green-200 rounded-lg p-8 text-center mb-8">
        <div class="text-green-600 text-6xl mb-4">&#10003;</div>
        <h1 class="text-3xl font-bold text-green-800 mb-2">{{ __('Zamówienie złożone!') }}</h1>
        <p class="text-green-700">
            {{ __('Twoje zamówienie nr') }} <strong>#{{ $order->id }}</strong>
            {{ __('zostało przyjęte i oczekuje na płatność.') }}
        </p>
    </div>

    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-xl font-semibold mb-4">{{ __('Szczegóły zamówienia') }}</h2>

        <div class="space-y-3">
            @foreach($order->items as $item)
                <div class="flex justify-between py-2 border-b">
                    <div>
                        <span class="font-medium">{{ $item->product->name }}</span>
                        @if($item->variantSize)
                            <span class="text-gray-500 text-sm">({{ $item->variantSize->size }})</span>
                        @endif
                        <span class="text-gray-500"> × {{ $item->qty }}</span>
                    </div>
                    <span>{{ number_format($item->subtotal() / 100, 2, ',', '') }} zł</span>
                </div>
            @endforeach

            @if($order->shipping_grosze > 0)
                <div class="flex justify-between py-2 border-b text-gray-600">
                    <span>{{ __('Dostawa') }}</span>
                    <span>{{ number_format($order->shipping_grosze / 100, 2, ',', '') }} zł</span>
                </div>
            @endif

            <div class="flex justify-between py-2 font-bold text-xl">
                <span>{{ __('Razem') }}</span>
                <span>{{ $order->displayTotal() }}</span>
            </div>
        </div>
    </div>

    @if($order->isPaid())
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-blue-800 mb-6">
            {{ __('Płatność została potwierdzona.') }}
        </div>
    @else
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-yellow-800 mb-6">
            {{ __('Oczekujemy na potwierdzenie płatności. Po zaksięgowaniu przelewu otrzymasz e-mail z potwierdzeniem.') }}
        </div>
    @endif

    <div class="text-center">
        <a href="{{ route('shop.index') }}" class="bg-yellow-400 text-black px-6 py-3 rounded-lg font-semibold hover:bg-yellow-500 inline-block">
            &larr; {{ __('Powrót do sklepu') }}
        </a>
    </div>
</div>
@endsection

@if($order->isPaid())
    @push('cookie-scripts')
        @include('partials.analytics-event', [
            'eventName' => 'purchase',
        ])
    @endpush
@endif

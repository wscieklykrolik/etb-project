@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-5xl px-6 py-10 text-white">
    <h1 class="mb-6 text-3xl font-black">{{ __('Koszyk') }}</h1>

    @if($items->isEmpty())
        <div class="rounded-lg border border-zinc-700 bg-white p-8 text-center text-zinc-950">
            <p class="mb-4 text-lg font-semibold text-zinc-700">{{ __('Twój koszyk jest pusty.') }}</p>
            <a href="{{ route('shop.index') }}" class="inline-flex rounded-lg bg-yellow-400 px-6 py-3 font-black text-black hover:bg-yellow-300">
                ← {{ __('Przejdź do sklepu') }}
            </a>
        </div>
    @else
        <form id="cart-update" method="POST" action="{{ route('cart.update') }}">
            @csrf
        </form>

        <div class="overflow-hidden rounded-lg border border-zinc-300 bg-white text-zinc-950 shadow">
            <div class="grid grid-cols-[1fr_7rem_7rem_7rem_4rem] gap-3 border-b border-zinc-200 bg-zinc-100 px-5 py-3 text-sm font-black uppercase tracking-wide text-zinc-700 max-lg:hidden">
                <div>{{ __('Produkt') }}</div>
                <div class="text-center">{{ __('Ilość') }}</div>
                <div class="text-right">{{ __('Cena') }}</div>
                <div class="text-right">{{ __('Suma') }}</div>
                <div></div>
            </div>

            <div class="divide-y divide-zinc-200">
                @foreach($items as $item)
                    @php($image = $item->product->images[0] ?? null)
                    <div class="grid gap-4 px-5 py-4 lg:grid-cols-[1fr_7rem_7rem_7rem_4rem] lg:items-center">
                        <div class="flex min-w-0 items-center gap-4">
                            <div class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-lg border border-zinc-300 bg-zinc-900">
                                @if($image)
                                    <img src="{{ asset('storage/'.$image) }}" alt="{{ $item->product->name }}" class="h-full w-full object-cover">
                                @else
                                    <span class="text-xl font-black text-zinc-500">{{ strtoupper(substr($item->product->name, 0, 2)) }}</span>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <p class="truncate font-black text-zinc-950">{{ $item->product->name }}</p>
                                @if($item->variant)
                                    <p class="mt-1 text-sm font-semibold text-zinc-500">Rozmiar: {{ $item->variant->size_label }}</p>
                                @endif
                            </div>
                        </div>

                        <div class="lg:text-center">
                            <input form="cart-update" type="hidden" name="items[{{ $loop->index }}][product_id]" value="{{ $item->product->id }}">
                            <input form="cart-update" type="hidden" name="items[{{ $loop->index }}][variant_size_id]" value="{{ $item->variant?->id }}">
                            <label class="mb-1 block text-xs font-bold uppercase text-zinc-500 lg:hidden">Ilość</label>
                            <input form="cart-update" type="number" name="items[{{ $loop->index }}][qty]" value="{{ $item->qty }}" min="0" max="99" class="w-20 rounded border border-zinc-400 px-2 py-1 text-center text-zinc-950 focus:border-yellow-400 focus:ring-yellow-400">
                        </div>

                        <div class="text-sm lg:text-right">
                            <span class="font-bold text-zinc-500 lg:hidden">Cena: </span>
                            {{ number_format($item->unit_price_grosze / 100, 2, ',', '') }} zł
                        </div>
                        <div class="font-black lg:text-right">
                            <span class="font-bold text-zinc-500 lg:hidden">Suma: </span>
                            {{ number_format($item->subtotal_grosze / 100, 2, ',', '') }} zł
                        </div>
                        <form method="POST" action="{{ route('cart.remove') }}" class="lg:text-right">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $item->product->id }}">
                            <input type="hidden" name="variant_size_id" value="{{ $item->variant?->id }}">
                            <button class="rounded border border-red-200 px-3 py-1.5 text-sm font-bold text-red-600 hover:bg-red-50">{{ __('Usuń') }}</button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mt-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="text-2xl font-black">
                {{ __('Razem') }}: {{ number_format($totalGrosze / 100, 2, ',', '') }} zł
            </div>
            <div class="flex flex-wrap gap-3">
                <button type="submit" form="cart-update" class="rounded-lg border border-zinc-300 px-4 py-2 font-bold text-white hover:border-yellow-400 hover:text-yellow-400">
                    {{ __('Aktualizuj koszyk') }}
                </button>
                <a href="{{ route('checkout.shipping') }}" class="rounded-lg bg-yellow-400 px-6 py-2 font-black text-black hover:bg-yellow-300">
                    {{ __('Przejdź do kasy') }} →
                </a>
            </div>
        </div>
    @endif
</div>
@endsection

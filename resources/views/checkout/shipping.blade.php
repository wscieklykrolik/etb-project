@extends('layouts.app')

@section('content')
@php
    $selectedMethod = old('shipping_method', array_key_first(config('shipping.methods', [])));
    $lockerPoints = [
        ['name' => 'LOD01A', 'address' => 'Piotrkowska 120', 'city' => 'Łódź'],
        ['name' => 'LOD22M', 'address' => 'al. Kościuszki 47', 'city' => 'Łódź'],
        ['name' => 'LOD45N', 'address' => 'ul. Widzewska 18', 'city' => 'Łódź'],
    ];
@endphp

<div class="mx-auto max-w-3xl px-6 py-10 text-white">
    <h1 class="mb-6 text-3xl font-black">{{ __('Dostawa') }}</h1>

    <div class="mb-6 rounded-lg border border-zinc-300 bg-white p-6 text-zinc-950 shadow">
        <h2 class="mb-4 text-xl font-black">{{ __('Podsumowanie koszyka') }}</h2>
        @foreach($items as $item)
            <div class="flex justify-between gap-4 border-b border-zinc-200 py-2 text-sm">
                <span>{{ $item->product->name }} × {{ $item->qty }}</span>
                <span class="font-bold">{{ number_format($item->subtotal_grosze / 100, 2, ',', '') }} zł</span>
            </div>
        @endforeach
        <div class="flex justify-between py-3 text-lg font-black">
            <span>{{ __('Razem') }}</span>
            <span>{{ number_format($totalGrosze / 100, 2, ',', '') }} zł</span>
        </div>
    </div>

    <form method="POST" action="{{ route('checkout.shipping') }}" class="space-y-6" x-data="{
        method: @js($selectedMethod),
        lockerName: @js(old('address.locker_name', '')),
        lockerAddress: @js(old('address.locker_address', '')),
        lockerCity: @js(old('address.locker_city', '')),
        chooseLocker(point) {
            this.lockerName = point.name;
            this.lockerAddress = point.address;
            this.lockerCity = point.city;
        },
        isLocker() {
            return ['inpost_locker', 'dpd_locker'].includes(this.method);
        },
        isCourier() {
            return ['inpost_courier', 'dpd_courier'].includes(this.method);
        }
    }">
        @csrf

        @if($needsShipping)
            <div class="rounded-lg border border-zinc-300 bg-white p-6 text-zinc-950 shadow">
                <h2 class="mb-4 text-xl font-black">{{ __('Metoda dostawy') }}</h2>

                <div class="space-y-3">
                    @foreach(config('shipping.methods', []) as $value => $method)
                        <label class="flex cursor-pointer items-center gap-3 rounded-lg border p-3 transition"
                               :class="method === @js($value) ? 'border-yellow-400 bg-yellow-100 ring-2 ring-yellow-300' : 'border-zinc-300 bg-white hover:border-yellow-400'">
                            <input type="radio" name="shipping_method" value="{{ $value }}" x-model="method" class="text-yellow-500 focus:ring-yellow-400">
                            <span class="flex flex-1 items-center justify-between gap-3">
                                <span class="font-black">{{ $method['label'] }}</span>
                                <span class="font-bold text-zinc-700">
                                    @if($method['price_grosze'] > 0)
                                        {{ number_format($method['price_grosze'] / 100, 2, ',', '') }} zł
                                    @else
                                        {{ __('Bezpłatna') }}
                                    @endif
                                </span>
                            </span>
                        </label>
                    @endforeach
                </div>
                @error('shipping_method') <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="rounded-lg border border-zinc-300 bg-white p-6 text-zinc-950 shadow">
                <h2 class="mb-4 text-xl font-black">{{ __('Dane odbiorcy') }}</h2>

                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="mb-1 block text-sm font-bold">{{ __('Imię i nazwisko odbiorcy') }}</label>
                        <input type="text" name="address[recipient_name]" value="{{ old('address.recipient_name', auth()->user()?->name) }}" class="w-full rounded border border-zinc-400 px-3 py-2 text-zinc-950 focus:border-yellow-400 focus:ring-yellow-400" required>
                        @error('address.recipient_name') <p class="mt-1 text-sm font-semibold text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div x-show="isLocker()" x-cloak class="rounded-lg border border-zinc-300 bg-white p-6 text-zinc-950 shadow">
                <div class="mb-4 flex flex-col gap-1">
                    <h2 class="text-xl font-black">{{ __('Wybór paczkomatu') }}</h2>
                    <p class="text-sm text-zinc-600">Wybierz punkt na mapie poglądowej albo wpisz dokładne dane paczkomatu ręcznie.</p>
                </div>

                <input type="hidden" name="address[locker_name]" x-model="lockerName">
                <input type="hidden" name="address[locker_address]" x-model="lockerAddress">
                <input type="hidden" name="address[locker_city]" x-model="lockerCity">

                <div class="grid gap-4 lg:grid-cols-[1.1fr_0.9fr]">
                    <div class="relative min-h-72 overflow-hidden rounded-lg border border-zinc-300 bg-zinc-100">
                        <div class="absolute inset-0 opacity-60" style="background-image: linear-gradient(#d4d4d8 1px, transparent 1px), linear-gradient(90deg, #d4d4d8 1px, transparent 1px); background-size: 34px 34px;"></div>
                        <div class="absolute left-6 top-8 h-8 w-32 rounded bg-white/80"></div>
                        <div class="absolute bottom-8 right-8 h-8 w-40 rounded bg-white/80"></div>
                        @foreach($lockerPoints as $point)
                            <button type="button"
                                    class="absolute rounded-full border-2 border-white bg-yellow-400 px-3 py-2 text-xs font-black text-black shadow-lg transition hover:scale-105"
                                    style="left: {{ [22, 58, 38][$loop->index] }}%; top: {{ [30, 48, 68][$loop->index] }}%;"
                                    @click="chooseLocker(@js($point))">
                                {{ $point['name'] }}
                            </button>
                        @endforeach
                    </div>

                    <div class="space-y-3">
                        @foreach($lockerPoints as $point)
                            <button type="button" class="w-full rounded-lg border border-zinc-300 p-3 text-left transition hover:border-yellow-400 hover:bg-yellow-50" @click="chooseLocker(@js($point))">
                                <span class="block font-black">{{ $point['name'] }}</span>
                                <span class="block text-sm text-zinc-600">{{ $point['address'] }}, {{ $point['city'] }}</span>
                            </button>
                        @endforeach

                        <div class="rounded-lg border border-yellow-300 bg-yellow-50 p-3">
                            <p class="text-xs font-bold uppercase tracking-wide text-yellow-800">Wybrany paczkomat</p>
                            <p class="mt-1 font-black" x-text="lockerName || 'Nie wybrano'"></p>
                            <p class="text-sm text-zinc-700"><span x-text="lockerAddress"></span><span x-show="lockerCity">, </span><span x-text="lockerCity"></span></p>
                        </div>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div>
                        <label class="mb-1 block text-sm font-bold">{{ __('Kod paczkomatu') }}</label>
                        <input type="text" x-model="lockerName" class="w-full rounded border border-zinc-400 px-3 py-2 text-zinc-950 focus:border-yellow-400 focus:ring-yellow-400">
                        @error('address.locker_name') <p class="mt-1 text-sm font-semibold text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-bold">{{ __('Adres paczkomatu') }}</label>
                        <input type="text" x-model="lockerAddress" class="w-full rounded border border-zinc-400 px-3 py-2 text-zinc-950 focus:border-yellow-400 focus:ring-yellow-400">
                        @error('address.locker_address') <p class="mt-1 text-sm font-semibold text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-bold">{{ __('Miasto paczkomatu') }}</label>
                        <input type="text" x-model="lockerCity" class="w-full rounded border border-zinc-400 px-3 py-2 text-zinc-950 focus:border-yellow-400 focus:ring-yellow-400">
                        @error('address.locker_city') <p class="mt-1 text-sm font-semibold text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div x-show="isCourier()" x-cloak class="rounded-lg border border-zinc-300 bg-white p-6 text-zinc-950 shadow">
                <h2 class="mb-4 text-xl font-black">{{ __('Adres dostawy') }}</h2>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-bold">{{ __('Ulica i numer') }}</label>
                        <input type="text" name="address[street]" value="{{ old('address.street') }}" class="w-full rounded border border-zinc-400 px-3 py-2 text-zinc-950 focus:border-yellow-400 focus:ring-yellow-400">
                        @error('address.street') <p class="mt-1 text-sm font-semibold text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-bold">{{ __('Miasto') }}</label>
                        <input type="text" name="address[city]" value="{{ old('address.city') }}" class="w-full rounded border border-zinc-400 px-3 py-2 text-zinc-950 focus:border-yellow-400 focus:ring-yellow-400">
                        @error('address.city') <p class="mt-1 text-sm font-semibold text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-bold">{{ __('Kod pocztowy') }}</label>
                        <input type="text" name="address[postal_code]" value="{{ old('address.postal_code') }}" class="w-full rounded border border-zinc-400 px-3 py-2 text-zinc-950 focus:border-yellow-400 focus:ring-yellow-400">
                        @error('address.postal_code') <p class="mt-1 text-sm font-semibold text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-bold">{{ __('Kraj') }}</label>
                        <input type="text" name="address[country]" value="{{ old('address.country', 'Polska') }}" class="w-full rounded border border-zinc-400 px-3 py-2 text-zinc-950 focus:border-yellow-400 focus:ring-yellow-400">
                        @error('address.country') <p class="mt-1 text-sm font-semibold text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        @else
            <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 text-blue-800">
                {{ __('Twoje zamówienie zawiera tylko produkty cyfrowe - pomijamy krok dostawy.') }}
            </div>
        @endif

        <div class="flex items-center justify-between">
            <a href="{{ route('cart.index') }}" class="text-zinc-400 hover:text-yellow-400">← {{ __('Powrót do koszyka') }}</a>
            <button type="submit" class="rounded-lg bg-yellow-400 px-6 py-3 font-black text-black hover:bg-yellow-300">
                {{ __('Dalej: Płatność') }} →
            </button>
        </div>
    </form>
</div>
@endsection

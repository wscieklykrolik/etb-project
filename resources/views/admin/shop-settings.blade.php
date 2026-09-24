@extends('layouts.admin')
@section('title', 'Ustawienia zamówień')
@section('admin-content')
<div class="mx-auto max-w-3xl space-y-6">
    <h1 class="text-2xl font-black">Ustawienia zamówień</h1>
    <p class="text-slate-600">Wygląd sklepu i produkty pozostają bez zmian. Obecny sposób zamawiania: <strong>{{ $legacy ? 'dotychczasowy sklep z płatnościami online' : 'wiadomość na Instagramie lub e-mail' }}</strong>.</p>
    @if(session('success'))<p role="status" class="rounded-lg bg-green-100 p-4 text-green-900">{{ session('success') }}</p>@endif
    @foreach($errors->all() as $error)<p role="alert" class="rounded-lg bg-red-100 p-3 text-red-800">{{ $error }}</p>@endforeach
    <p class="rounded-lg border border-slate-200 bg-white p-4 text-sm">Wysyłka: publiczny cennik Szybkich Nadań InPost, gabaryt B (19 × 38 × 64 cm, do 25 kg), odświeżany automatycznie co 6 godzin. @if($quote['price_grosze'] !== null)<strong>Obecnie: {{ number_format($quote['price_grosze'] / 100, 2, ',', ' ') }} zł.</strong>@else Nie udało się pobrać aktualnej ceny; odbiór na meczu pozostaje dostępny.@endif Odbiór na meczu zawsze 0,00 zł.</p>
    <form method="POST" action="{{ route('admin.shop-settings.update') }}" class="space-y-5 rounded-lg border border-slate-200 bg-white p-5">
        @csrf @method('PUT')
        <div><label for="order-email" class="block font-bold">E-mail do zamówień</label><input id="order-email" type="email" name="email" maxlength="254" value="{{ old('email', $contact['email']) }}" class="mt-2 w-full rounded-lg border-slate-300"><p class="mt-1 text-sm text-slate-500">Pozostaw puste, aby pokazywać wyłącznie kontakt przez Instagram.</p></div>
        <p class="text-sm text-slate-600">Instagram: <a href="https://www.instagram.com/eat_the_ball/" class="underline">@eat_the_ball</a></p>
        @foreach(['payment' => ['Instrukcja płatności', 3000], 'inpost' => ['Informacje o wysyłce InPost', 1500], 'pickup' => ['Informacje o bezpłatnym odbiorze na meczu', 1500]] as $field => [$label, $limit])
            <div><label for="order-{{ $field }}" class="block font-bold">{{ $label }}</label><textarea id="order-{{ $field }}" name="{{ $field }}" required maxlength="{{ $limit }}" rows="4" class="mt-2 w-full rounded-lg border-slate-300">{{ old($field, $contact[$field]) }}</textarea></div>
        @endforeach
        <button class="rounded-lg bg-yellow-400 px-5 py-3 font-black text-black">Zapisz informacje dla klientów</button>
    </form>
    <section class="rounded-lg border border-red-200 bg-white p-5" x-data>
        <h2 class="text-lg font-black">Zmiana sposobu zamawiania</h2><p class="mt-2 text-sm text-slate-600">Dostępne wyłącznie dla administratora. Zmiana dotyczy wszystkich klientów.</p>
        <button type="button" @click="$dispatch('open-modal', 'shop-mode-confirm')" class="mt-4 rounded-lg border border-red-300 px-5 py-3 font-bold text-red-800">{{ $legacy ? 'Włącz zamówienia przez wiadomości' : 'Powrót do „starego” sklepu' }}</button>
    </section>
    <x-modal name="shop-mode-confirm" :show="$errors->has('confirmation') || $errors->has('acknowledged')" focusable>
        <form method="POST" action="{{ route('admin.shop-settings.mode') }}" class="space-y-4 p-6 text-slate-950" role="dialog" aria-modal="true" aria-labelledby="shop-mode-title">
            @csrf @method('PATCH')
            <h2 id="shop-mode-title" class="text-xl font-black">Uwaga: zmieniasz sklep dla wszystkich klientów</h2>
            <p>{{ $legacy ? 'Włączysz zamówienia przez kopiowaną wiadomość i wyłączysz płatności online oraz automatyczne etykiety.' : 'Przywrócisz dotychczasową kasę, Przelewy24 i mechanizmy wysyłki. Klienci stracą generator wiadomości. Integracje muszą być skonfigurowane i przetestowane przed włączeniem. Obecna infrastruktura zawiera niedokończone integracje — sam ten przycisk ich nie naprawia.' }}</p>
            <input type="hidden" name="mode" value="{{ $legacy ? 'message' : 'legacy' }}">
            <label class="flex items-start gap-3"><input type="checkbox" name="acknowledged" value="1" required class="mt-1"><span>Rozumiem skutki zmiany i chcę zmienić tryb dla całej strony.</span></label>
            <div><label for="mode-confirmation" class="block font-bold">Wpisz: ZMIENIAM TRYB SKLEPU</label><input id="mode-confirmation" name="confirmation" required autocomplete="off" class="mt-2 w-full rounded-lg border-slate-300"></div>
            <div class="flex flex-wrap justify-end gap-3"><button type="button" @click="$dispatch('close-modal', 'shop-mode-confirm')" class="rounded-lg border border-slate-300 px-4 py-2 font-bold">Anuluj</button><button class="rounded-lg bg-red-700 px-4 py-2 font-bold text-white">Potwierdzam zmianę trybu</button></div>
        </form>
    </x-modal>
</div>
@endsection

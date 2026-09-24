@extends('layouts.app')
@section('content')
<div class="mx-auto max-w-5xl px-6 py-10 text-white" x-data="{ deliveryChanged: false }">
    <a href="{{ route('cart.index') }}" class="text-sm font-bold text-yellow-400 hover:underline">← Powrót do koszyka</a>
    <h1 class="mt-5 text-3xl font-black">Zamów przez wiadomość</h1>
    <p class="mt-3 text-zinc-300">Wybierz dostawę, skopiuj zamówienie i wyślij je do nas na Instagramie lub e-mailem. Potwierdzimy dostępność i szczegóły płatności.</p>
    @foreach ($errors->all() as $error)<p role="alert" class="mt-3 rounded-lg bg-red-100 p-3 font-bold text-red-800">{{ $error }}</p>@endforeach
    <div class="mt-6 rounded-lg border border-zinc-300 bg-white p-5 text-zinc-950">
        <h2 class="text-xl font-black">Twoje produkty</h2>
        <ul class="mt-3 divide-y divide-zinc-200">
            @foreach ($items as $item)
                <li class="flex flex-wrap justify-between gap-3 py-3">
                    <div><span class="font-bold">{{ $item->product->name }}</span><span class="mt-1 block text-sm text-zinc-600">ID: {{ $item->product->id }} · Ilość: {{ $item->qty }}@if($item->variant) · Rozmiar: {{ $item->variant->size_label }}@endif</span></div>
                    <span class="font-black">{{ number_format($item->subtotal_grosze / 100, 2, ',', ' ') }} zł</span>
                </li>
            @endforeach
        </ul>
        <p class="mt-3 border-t border-zinc-200 pt-4 text-xl font-black">Suma produktów: {{ number_format($totalGrosze / 100, 2, ',', ' ') }} zł</p>
    </div>
    <form method="POST" action="{{ route('message-order.generate') }}" class="mt-6 rounded-lg border border-zinc-700 bg-zinc-900 p-5">
        @csrf
        <fieldset @change="deliveryChanged = true"><legend class="text-xl font-black">Wybierz dostawę</legend>
            <label class="mt-4 flex items-start gap-3 rounded-lg border border-zinc-700 p-4"><input type="radio" name="delivery" value="inpost" required @disabled($quote['price_grosze'] === null) @checked(old('delivery', $delivery) === 'inpost') class="mt-1 text-yellow-500 focus:ring-yellow-400"><span><strong class="block">Paczkomat InPost — gabaryt B @if($quote['price_grosze'] !== null) · {{ number_format($quote['price_grosze'] / 100, 2, ',', ' ') }} zł @endif</strong><span class="mt-1 block text-sm text-zinc-300">{{ $contact['inpost'] }}</span></span></label>
            <label class="mt-3 flex items-start gap-3 rounded-lg border border-zinc-700 p-4"><input type="radio" name="delivery" value="pickup" required @checked(old('delivery', $delivery) === 'pickup') class="mt-1 text-yellow-500 focus:ring-yellow-400"><span><strong class="block">Odbiór osobisty na meczu — bezpłatnie</strong><span class="mt-1 block text-sm text-zinc-300">{{ $contact['pickup'] }}</span></span></label>
        </fieldset>
        @if($quote['price_grosze'] === null)
            <p role="status" class="mt-3 text-sm text-yellow-300">Nie możemy teraz potwierdzić ceny InPost. Spróbuj ponownie później lub wybierz bezpłatny odbiór na meczu.</p>
        @else
            <p class="mt-3 text-xs text-zinc-400">Publiczny <a href="https://inpost.pl/cenniki" target="_blank" rel="noopener noreferrer" class="underline">cennik InPost Szybkie Nadania</a>. Cena brutto dla jednej paczki B, odświeżana co 6 godzin. Sprawdzono: {{ \Carbon\Carbon::parse($quote['checked_at'])->timezone('Europe/Warsaw')->format('d.m.Y H:i') }}.</p>
        @endif
        <button type="submit" class="mt-5 rounded-lg bg-yellow-400 px-6 py-3 font-black text-black hover:bg-yellow-300">{{ $orderText ? 'Wygeneruj tekst ponownie' : 'Wygeneruj tekst zamówienia' }}</button>
        <p class="mt-3 text-sm text-zinc-400">Wygenerowanie tekstu nie wysyła zamówienia ani nie pobiera płatności. Po zmianie dostawy wygeneruj tekst ponownie.</p>
    </form>
    @if ($orderText)
        <section x-show="!deliveryChanged" id="order-message" class="mt-6 rounded-lg border border-yellow-400 bg-white p-5 text-zinc-950" x-data="{ copyStatus: '' }">
            <h2 class="text-xl font-black">Skopiuj i wyślij zamówienie</h2>
            <label for="order-text" class="mt-4 block text-sm font-bold">Treść wiadomości</label>
            <textarea id="order-text" x-ref="orderText" readonly rows="14" class="mt-2 w-full rounded-lg border-zinc-300 bg-zinc-50 text-sm">{{ $orderText }}</textarea>
            <button type="button" @click="try { await navigator.clipboard.writeText($refs.orderText.value); copyStatus = 'Skopiowano zamówienie. Wklej je w wiadomości.'; } catch (error) { $refs.orderText.focus(); $refs.orderText.select(); copyStatus = 'Zaznaczono tekst. Skopiuj go ręcznie z menu urządzenia lub skrótem Ctrl+C.'; }" class="mt-3 rounded-lg bg-yellow-400 px-5 py-3 font-black text-black hover:bg-yellow-300">Kopiuj zamówienie</button>
            <p aria-live="polite" x-text="copyStatus" class="mt-2 text-sm font-bold text-zinc-700"></p>
            <h3 class="mt-6 text-lg font-black">Co dalej?</h3>
            <ol class="mt-3 list-decimal space-y-2 pl-5 text-zinc-700"><li>Skopiuj powyższy tekst.</li><li>Otwórz wiadomość na Instagramie lub e-mail, wklej tekst i wyślij go do nas.</li><li>Poczekaj na potwierdzenie dostępności, kwoty i realizacji.</li></ol>
            <div class="mt-5 flex flex-wrap gap-3">
                <a href="https://ig.me/m/eat_the_ball/" target="_blank" rel="noopener noreferrer" class="rounded-lg bg-zinc-950 px-5 py-3 font-black text-white">Napisz na Instagramie</a>
                @if ($contact['email'])<a href="mailto:{{ $contact['email'] }}?subject={{ rawurlencode('Zamówienie Eat The Ball') }}" class="rounded-lg border border-zinc-400 px-5 py-3 font-bold">Napisz e-mail</a>@endif
            </div>
            <p class="mt-3 text-sm text-zinc-600">Jeśli rozmowa się nie otworzy, przejdź na <a href="https://www.instagram.com/eat_the_ball/" target="_blank" rel="noopener noreferrer" class="font-bold underline">profil @eat_the_ball</a> i wybierz „Wiadomość”.</p>
            @if ($contact['email'])<p class="mt-2 text-sm">E-mail: {{ $contact['email'] }}</p>@endif
            <h3 class="mt-6 font-black">Jak zapłacić?</h3><p class="mt-2 whitespace-pre-line text-zinc-700">{{ $contact['payment'] }}</p>
        </section>
    @endif
</div>
@endsection

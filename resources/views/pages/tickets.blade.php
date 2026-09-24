@extends('layouts.app')

@section('content')
<section class="mx-auto max-w-7xl px-6 py-12 text-white">
    <div class="grid gap-8 lg:grid-cols-[1fr_minmax(18rem,34rem)] lg:items-center">
        <div>
            <div class="flex flex-col gap-5 sm:flex-row sm:items-center">
                <div class="flex h-28 w-28 shrink-0 items-center justify-center rounded-lg bg-white p-3">
                    <x-site-logo :url="$ticketsLogoUrl" alt="Logo biletów" image-class="max-h-full max-w-full object-contain" fallback="Bilety" fallback-class="text-center text-sm font-black uppercase tracking-wide text-black" />
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-yellow-400">Bilety</h1>
                    <p class="mt-3 max-w-2xl text-zinc-300">Informacje o wejściówkach, sprzedaży i dostępności miejsc na mecze ETB Łódź.</p>
                </div>
            </div>

            <div class="mt-8 rounded-lg border border-zinc-700 bg-zinc-900 p-6">
                @if (filled($ticketsPageBody))
                    <div class="whitespace-pre-line break-words text-base leading-8 text-zinc-100">{{ $ticketsPageBody }}</div>
                @else
                    <p class="text-sm text-zinc-400">Treść strony biletów zostanie wkrótce uzupełniona.</p>
                @endif

                @if (filled($ticketsPageButtonUrl))
                    <a href="{{ $ticketsPageButtonUrl }}" target="_blank" rel="noopener noreferrer" class="mt-6 inline-flex items-center justify-center gap-2 rounded-lg bg-yellow-400 px-5 py-3 text-sm font-black uppercase tracking-wide text-black transition hover:bg-yellow-300">
                        {{ filled($ticketsPageButtonLabel) ? $ticketsPageButtonLabel : 'Kup bilety' }}
                        <i data-lucide="arrow-up-right" class="h-4 w-4"></i>
                    </a>
                @endif
            </div>
        </div>

        @if ($ticketsPageImageUrl)
            <figure class="overflow-hidden rounded-lg border border-zinc-700 bg-zinc-900">
                <img src="{{ $ticketsPageImageUrl }}" alt="Grafika strony biletów" class="h-auto w-full object-cover">
            </figure>
        @endif
    </div>
</section>
@endsection

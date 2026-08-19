@extends('layouts.app')

@section('content')
@php($logo = $match->opponent_logo ?: $match->opponent?->logo_path)
<section class="mx-auto max-w-5xl px-4 py-12 sm:px-6 lg:px-8">
    <a href="{{ url()->previous() }}" class="text-sm font-semibold text-yellow-400 hover:text-yellow-300">← Wróć</a>

    <article class="mt-8 rounded-lg border border-zinc-800 bg-zinc-950 p-8 shadow-2xl">
        <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-5">
                <div class="flex h-24 w-24 items-center justify-center rounded bg-white p-3">
                    @if ($logo)
                        <img src="{{ \App\Support\MediaStorage::url($logo) }}" alt="{{ $match->opponent_name }}" class="max-h-full max-w-full object-contain">
                    @else
                        <span class="text-xs font-black text-zinc-500">LOGO</span>
                    @endif
                </div>
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.25em] text-yellow-400">{{ $match->is_home ? 'Mecz domowy' : 'Mecz wyjazdowy' }}</p>
                    <h1 class="mt-2 text-4xl font-black text-white">ETB - {{ $match->opponent_name }}</h1>
                </div>
            </div>

            <div class="text-left md:text-right">
                @if ($match->hasResult())
                    <p class="text-5xl font-black {{ $match->isWin() ? 'text-emerald-400' : 'text-red-400' }}">{{ $match->our_score }}:{{ $match->opponent_score }}</p>
                    <p class="text-sm font-bold uppercase tracking-widest {{ $match->isWin() ? 'text-emerald-400' : 'text-red-400' }}">{{ $match->isWin() ? 'Zwycięstwo' : 'Porażka' }}</p>
                @else
                    <p class="text-5xl font-black text-white">--:--</p>
                    <p class="text-sm font-bold uppercase tracking-widest text-zinc-400">Do rozegrania</p>
                @endif
            </div>
        </div>

        @if ($match->ticketSalesActive())
            <div class="mt-8">
                <a href="{{ $match->ticket_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex rounded bg-yellow-400 px-5 py-3 text-sm font-black uppercase tracking-wide text-black transition hover:bg-yellow-300">
                    Kup bilety
                </a>
            </div>
        @endif

        <dl class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded border border-zinc-800 bg-zinc-900 p-4">
                <dt class="text-xs uppercase tracking-widest text-zinc-500">Data</dt>
                <dd class="mt-1 font-bold text-white">{{ $match->match_date?->translatedFormat('d F Y') }}</dd>
            </div>
            <div class="rounded border border-zinc-800 bg-zinc-900 p-4">
                <dt class="text-xs uppercase tracking-widest text-zinc-500">Godzina</dt>
                <dd class="mt-1 font-bold text-white">{{ $match->match_date?->format('H:i') }}</dd>
            </div>
            <div class="rounded border border-zinc-800 bg-zinc-900 p-4">
                <dt class="text-xs uppercase tracking-widest text-zinc-500">Lokalizacja</dt>
                <dd class="mt-1 font-bold text-white">{{ $match->location }}</dd>
            </div>
            <div class="rounded border border-zinc-800 bg-zinc-900 p-4">
                <dt class="text-xs uppercase tracking-widest text-zinc-500">Status</dt>
                <dd class="mt-1 font-bold text-white">{{ $match->status === \App\Models\TeamMatch::STATUS_FINISHED ? 'Zakończony' : 'Nadchodzący' }}</dd>
            </div>
        </dl>

        @if ($match->include_in_lzkosz)
            <div class="mt-4 rounded border border-zinc-800 bg-zinc-900 p-4">
                <p class="text-xs uppercase tracking-widest text-zinc-500">Terminarz ŁZKosz</p>
                <p class="mt-1 font-bold text-white">{{ $match->lzkoszRoundLabel() }}</p>
            </div>
        @endif

        @if ($match->notes)
            <div class="prose prose-invert mt-8 max-w-none text-zinc-300">{!! nl2br(e($match->notes)) !!}</div>
        @endif
    </article>
</section>
@endsection


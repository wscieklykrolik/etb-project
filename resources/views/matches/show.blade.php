@extends('layouts.main')

@section('title', 'Szczegóły meczu')

@section('content')
    <div class="mx-auto max-w-3xl px-6 py-10">
        <article class="rounded-lg border bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 sm:flex-row">
                <x-match-teams :match="$match" class="max-w-xs text-gray-950" />

                <div>
                    <h1 class="text-3xl font-bold">ETB kontra {{ $match->opponent_name }}</h1>
                    <p class="mt-2 text-gray-600">{{ $match->is_home ? 'Mecz u siebie' : 'Mecz wyjazdowy' }}</p>
                </div>
            </div>

            <dl class="mt-6 grid gap-4 text-sm sm:grid-cols-2">
                <div>
                    <dt class="font-semibold text-gray-900">Data meczu</dt>
                    <dd class="text-gray-700">{{ $match->match_date->format('d.m.Y H:i') }}</dd>
                </div>

                <div>
                    <dt class="font-semibold text-gray-900">Lokalizacja</dt>
                    <dd class="text-gray-700">{{ $match->location }}</dd>
                </div>

                @if ($match->hasResult())
                    <div>
                        <dt class="font-semibold text-gray-900">Wynik</dt>
                        <dd class="text-gray-700">{{ $match->resultLabel() }}</dd>
                    </div>
                @endif
            </dl>
        </article>
    </div>
@endsection


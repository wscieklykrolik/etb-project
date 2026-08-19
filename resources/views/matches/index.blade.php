@extends('layouts.main')

@section('title', 'Mecze')

@section('content')
    <div class="mx-auto max-w-5xl px-6 py-10">
        <h1 class="mb-6 text-3xl font-bold">Terminarz i wyniki</h1>

        <div class="space-y-4">
            @forelse($matches as $match)
                <article class="rounded-lg border bg-white p-5 shadow-sm">
                    <div class="flex gap-4">
                        @if ($match->opponent_logo)
                            <img src="{{ \App\Support\MediaStorage::url($match->opponent_logo) }}"
                                 alt="Logo przeciwnika {{ $match->opponent_name }}"
                                 class="h-16 w-16 rounded object-contain ring-1 ring-gray-200">
                        @endif

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <h2 class="text-xl font-semibold">
                                        <a class="hover:underline" href="{{ route('matches.show', $match) }}">
                                            {{ $match->opponent_name }}
                                        </a>
                                    </h2>
                                    <p class="text-sm text-gray-600">{{ $match->match_date->format('d.m.Y H:i') }}</p>
                                </div>

                                <span class="w-fit rounded-full bg-gray-100 px-3 py-1 text-sm font-semibold text-gray-700">
                                    {{ $match->is_home ? 'U siebie' : 'Wyjazd' }}
                                </span>
                            </div>

                            <p class="mt-3 text-sm text-gray-700">Lokalizacja: {{ $match->location }}</p>

                            @if ($match->hasResult())
                                <p class="mt-1 text-sm font-semibold text-gray-900">Wynik: {{ $match->resultLabel() }}</p>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <p class="rounded-lg border border-dashed border-gray-300 bg-white p-6 text-gray-600">
                    Brak zaplanowanych meczów.
                </p>
            @endforelse
        </div>
    </div>
@endsection


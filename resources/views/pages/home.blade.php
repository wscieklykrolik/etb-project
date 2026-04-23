@extends('layouts.app')

@section('content')
    @include('partials.welcome')

    <section class="max-w-7xl mx-auto px-6 py-10 space-y-10">
        <div x-data="materialsCarousel(@js($latestArticles))" x-init="start()">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-2xl font-bold">Najnowsze materiały</h2>
                <div class="flex gap-2">
                    <template x-for="(chunk, index) in chunks" :key="index">
                        <button @click="goTo(index)" class="w-2.5 h-2.5 rounded-full transition"
                                :class="index === page ? 'bg-yellow-400' : 'bg-zinc-500'"
                                :aria-label="`Przejdź do strony ${index + 1}`"></button>
                    </template>
                </div>
            </div>
            <div class="grid md:grid-cols-4 gap-4">
                <template x-for="item in visibleItems" :key="item.title + item.date">
                    <article class="bg-zinc-900 border border-zinc-700 rounded-lg p-4">
                        <span class="text-xs uppercase tracking-wider text-yellow-400" x-text="item.category"></span>
                        <h3 class="font-semibold mt-2 mb-2" x-text="item.title"></h3>
                        <p class="text-sm text-zinc-400 mb-2" x-text="item.excerpt"></p>
                        <p class="text-xs text-zinc-500" x-text="item.date"></p>
                    </article>
                </template>
            </div>
        </div>

        <div>
            <h2 class="text-2xl font-bold mb-4">Terminarz przyszły (5x5)</h2>
            <div class="grid md:grid-cols-3 gap-4">
                @forelse($upcomingGames as $index => $game)
                    <div class="rounded-lg p-4 border {{ $index === 0 ? 'bg-yellow-400 text-black border-yellow-300' : 'bg-zinc-900 border-zinc-700' }}">
                        <p class="text-xs font-semibold uppercase mb-1 {{ $index === 0 ? 'text-black' : 'text-yellow-400' }}">
                            {{ $index === 0 ? 'Najbliższy mecz' : 'Mecz' }}
                        </p>
                        <h3 class="font-semibold">ETB vs {{ $game->opponent }}</h3>
                        <p class="text-sm {{ $index === 0 ? 'text-black/80' : 'text-zinc-400' }}">{{ \Carbon\Carbon::parse($game->match_date)->format('d.m.Y H:i') }}</p>
                        <p class="text-sm {{ $index === 0 ? 'text-black/80' : 'text-zinc-300' }}">{{ $game->location }}</p>
                    </div>
                @empty
                    <p class="text-zinc-400">Brak zaplanowanych meczów.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-zinc-900 border border-zinc-700 rounded-lg p-6">
            <h2 class="text-2xl font-bold mb-2">Następny turniej 3x3</h2>
            <p class="text-yellow-400 font-semibold">{{ $next3x3Tournament['name'] }}</p>
            <p class="text-zinc-300">{{ $next3x3Tournament['date'] }} • {{ $next3x3Tournament['place'] }}</p>
        </div>
    </section>
@endsection

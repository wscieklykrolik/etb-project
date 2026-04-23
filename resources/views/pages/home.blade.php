@extends('layouts.app')

@section('content')
    @include('partials.welcome')

    <section class="max-w-7xl mx-auto px-6 py-10 space-y-10">
        <div>
            <h2 class="text-2xl font-bold mb-4">4 najnowsze materiały</h2>
            <div class="grid md:grid-cols-4 gap-4">
                @foreach($latestArticles as $item)
                    <article class="bg-zinc-900 border border-zinc-700 rounded-lg p-4">
                        <span class="text-xs uppercase tracking-wider text-yellow-400">{{ $item['category'] }}</span>
                        <h3 class="font-semibold mt-2 mb-2">{{ $item['title'] }}</h3>
                        <p class="text-sm text-zinc-400 mb-2">{{ $item['excerpt'] }}</p>
                        <p class="text-xs text-zinc-500">{{ $item['date'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>

        <div>
            <h2 class="text-2xl font-bold mb-4">Terminarz przyszły (5x5)</h2>
            <div class="grid md:grid-cols-3 gap-4">
                @forelse($upcomingGames as $game)
                    <div class="bg-zinc-900 border border-zinc-700 rounded-lg p-4">
                        <h3 class="font-semibold">ETB vs {{ $game->opponent }}</h3>
                        <p class="text-sm text-zinc-400">{{ \Carbon\Carbon::parse($game->match_date)->format('d.m.Y H:i') }}</p>
                        <p class="text-sm text-zinc-300">{{ $game->location }}</p>
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

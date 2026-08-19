@extends('layouts.app')

@section('content')
<section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <div class="mb-10">
        <p class="text-sm font-bold uppercase tracking-[0.25em] text-yellow-400">ETB Basket</p>
        <h1 class="mt-2 text-4xl font-black text-white">Terminarz ŁZKosz</h1>
    </div>

    @foreach ([['title' => 'Runda 1', 'items' => $roundOneMatches], ['title' => 'Runda 2', 'items' => $roundTwoMatches]] as $group)
        <section class="mb-12">
            <h2 class="mb-5 border-l-4 border-yellow-400 pl-4 text-2xl font-black text-white">{{ $group['title'] }}</h2>
            <div class="overflow-hidden rounded-lg border border-zinc-800 bg-zinc-950">
                <div class="hidden grid-cols-[4rem_1.4fr_1fr_1fr_0.8fr_0.7fr] gap-4 border-b border-zinc-800 px-5 py-3 text-xs font-bold uppercase tracking-wide text-zinc-500 lg:grid">
                    <span>Logo</span>
                    <span>Przeciwnik</span>
                    <span>Data</span>
                    <span>Lokalizacja</span>
                    <span>Status</span>
                    <span class="text-right">Wynik</span>
                </div>

                <div class="divide-y divide-zinc-800">
                    @forelse ($group['items'] as $match)
                        @php($logo = $match->opponent_logo ?: $match->opponent?->logo_path)
                        <a href="{{ route('schedule.matches.show', $match) }}" class="grid gap-4 px-5 py-5 transition hover:bg-zinc-900 lg:grid-cols-[4rem_1.4fr_1fr_1fr_0.8fr_0.7fr] lg:items-center">
                            <div class="flex h-14 w-14 items-center justify-center rounded bg-white p-2">
                                @if ($logo)
                                    <img src="{{ \App\Support\MediaStorage::url($logo) }}" alt="{{ $match->opponent_name }}" class="max-h-full max-w-full object-contain">
                                @else
                                    <span class="text-xs font-black text-zinc-500">LOGO</span>
                                @endif
                            </div>
                            <div>
                                <h3 class="text-lg font-black text-white">{{ $match->opponent_name }}</h3>
                                <p class="text-sm text-zinc-400">{{ $match->is_home ? 'Domowy' : 'Wyjazdowy' }}</p>
                            </div>
                            <div class="text-sm font-semibold text-zinc-300">
                                {{ $match->match_date?->format('d.m.Y') }}
                                <span class="block text-yellow-400">{{ $match->match_date?->format('H:i') }}</span>
                            </div>
                            <p class="text-sm text-zinc-300">{{ $match->location }}</p>
                            <p class="text-sm font-bold uppercase tracking-wide text-zinc-300">{{ $match->status === \App\Models\TeamMatch::STATUS_FINISHED ? 'Zakończony' : 'Nadchodzący' }}</p>
                            <p class="text-left text-2xl font-black text-white lg:text-right">
                                {{ $match->status === \App\Models\TeamMatch::STATUS_FINISHED ? $match->our_score.' : '.$match->opponent_score : '-- : --' }}
                            </p>
                        </a>
                    @empty
                        <p class="p-6 text-zinc-400">Brak meczów w tej rundzie.</p>
                    @endforelse
                </div>
            </div>
        </section>
    @endforeach
</section>
@endsection


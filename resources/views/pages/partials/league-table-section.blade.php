@php
    $sectionId = $sectionId ?? null;
    $headingLevel = $headingLevel ?? 1;
@endphp

<section @if($sectionId) id="{{ $sectionId }}" @endif class="scroll-mt-28">
    <div class="mb-8">
        <p class="text-sm font-bold uppercase tracking-[0.25em] text-yellow-400">Rozgrywki</p>
        @if ($headingLevel === 1)
            <h1 class="mt-2 text-4xl font-black text-white">Tabela</h1>
        @else
            <h2 class="mt-2 text-3xl font-black text-white">Tabela</h2>
        @endif
        <p class="mt-3 max-w-3xl text-zinc-300">Tabela 3 Ligi Mężczyzn pobierana z ŁZKosz i wzbogacana logotypami z panelu ETB.</p>
    </div>

    <div class="overflow-hidden rounded-lg border border-zinc-800 bg-zinc-950 shadow-xl">
        <div class="overflow-x-auto">
            <table class="min-w-[58rem] w-full text-left text-sm">
                <thead class="border-b border-zinc-800 bg-zinc-900 text-xs uppercase tracking-wide text-zinc-400">
                    <tr>
                        <th class="px-4 py-3">M</th>
                        <th class="px-4 py-3">Drużyna</th>
                        <th class="px-4 py-3 text-center">Pkt</th>
                        <th class="px-4 py-3 text-center">Mecze</th>
                        <th class="px-4 py-3 text-center">Zw. - por.</th>
                        <th class="px-4 py-3 text-center">Dom</th>
                        <th class="px-4 py-3 text-center">Wyjazd</th>
                        <th class="px-4 py-3 text-center">Kosze</th>
                        <th class="px-4 py-3 text-center">Różnica</th>
                        <th class="px-4 py-3 text-center">Stosunek</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800">
                    @forelse ($leagueStandings as $standing)
                        <tr @class([
                            'transition hover:bg-zinc-900',
                            'bg-yellow-400/10' => str($standing->opponent->name)->lower()->contains('etb'),
                        ])>
                            <td class="px-4 py-4 text-lg font-black text-white">{{ $standing->position }}</td>
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded bg-white p-2">
                                        @if ($standing->opponent->logo_path)
                                            <img src="{{ \App\Support\MediaStorage::url($standing->opponent->logo_path) }}" alt="{{ $standing->opponent->name }}" class="max-h-full max-w-full object-contain">
                                        @else
                                            <span class="text-xs font-black text-zinc-500">LOGO</span>
                                        @endif
                                    </div>
                                    <span class="font-black text-white">{{ $standing->opponent->name }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-center font-bold text-white">{{ $standing->points }}</td>
                            <td class="px-4 py-4 text-center text-zinc-300">{{ $standing->games }}</td>
                            <td class="px-4 py-4 text-center text-zinc-300">{{ $standing->wins }} - {{ $standing->losses }}</td>
                            <td class="px-4 py-4 text-center text-zinc-300">{{ $standing->home_wins }} - {{ $standing->home_losses }}</td>
                            <td class="px-4 py-4 text-center text-zinc-300">{{ $standing->away_wins }} - {{ $standing->away_losses }}</td>
                            <td class="px-4 py-4 text-center text-zinc-300">{{ $standing->points_for }} - {{ $standing->points_against }}</td>
                            <td class="px-4 py-4 text-center font-bold {{ $standing->points_difference >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                                {{ $standing->points_difference > 0 ? '+' : '' }}{{ $standing->points_difference }}
                            </td>
                            <td class="px-4 py-4 text-center text-zinc-300">{{ number_format((float) $standing->ratio, 4) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-8 text-center text-zinc-400">Tabela nie została jeszcze pobrana. Użyj synchronizacji w panelu administratora.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($leagueStandings->isNotEmpty())
        <p class="mt-4 text-xs text-zinc-500">
            Ostatnia synchronizacja: {{ $leagueStandings->max('synced_at')?->format('d.m.Y H:i') ?? 'brak danych' }}.
            Źródło: ŁZKosz.
        </p>
    @endif
</section>


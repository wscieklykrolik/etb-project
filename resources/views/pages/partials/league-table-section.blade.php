@php
    $sectionId = $sectionId ?? null;
    $headingLevel = $headingLevel ?? 1;
    $leagueTableSnapshots = $leagueTableSnapshots ?? collect();
@endphp

<section @if($sectionId) id="{{ $sectionId }}" @endif class="scroll-mt-28">
    <div class="mb-8">
        <p class="text-sm font-bold uppercase tracking-[0.25em] text-yellow-400">Rozgrywki</p>
        @if ($headingLevel === 1)
            <h1 class="mt-2 text-4xl font-black text-white">Tabela</h1>
        @else
            <h2 class="mt-2 text-3xl font-black text-white">Tabela</h2>
        @endif
    </div>

    <div class="space-y-10">
        @if ($leagueStandings->isNotEmpty())
            <article>
                @if ($leagueTableSeason)
                    <h3 class="mb-4 text-xl font-black text-white">Sezon {{ $leagueTableSeason }}</h3>
                @endif
                @include('pages.partials.league-table-grid', ['rows' => $leagueStandings])
                @if($leagueStandings->contains(fn ($row) => !empty($row->last_five)))
                    <p class="mt-3 text-xs text-zinc-400">Forma: <span class="font-bold text-emerald-300">Z — zwycięstwo</span>, <span class="font-bold text-red-300">P — porażka</span>. Od najstarszego do najnowszego meczu.</p>
                @endif
                <p class="mt-4 text-xs text-zinc-500">
                    Ostatnia synchronizacja: {{ $leagueStandings->max('synced_at')?->format('d.m.Y H:i') ?? 'brak danych' }}.
                    Źródło: <a href="{{ $leagueTableSourceUrl }}" target="_blank" rel="noopener noreferrer" class="font-bold text-yellow-400 underline hover:text-white">{{ $leagueTableSourceLabel }}</a>.
                </p>
            </article>
        @endif

        @foreach ($leagueTableSnapshots as $snapshot)
            <article>
                <div class="mb-4">
                    <h3 class="text-xl font-black text-white">{{ $snapshot->title }}@if($snapshot->season) — {{ $snapshot->season }}@endif</h3>
                    @if ($snapshot->description)
                        <p class="mt-2 max-w-3xl text-sm text-zinc-300">{{ $snapshot->description }}</p>
                    @endif
                </div>
                @include('pages.partials.league-table-grid', ['rows' => $snapshot->rows])
                @if($snapshot->source_url)
                    <p class="mt-4 text-xs text-zinc-500">Źródło: <a href="{{ $snapshot->source_url }}" target="_blank" rel="noopener noreferrer" class="font-bold text-yellow-400 underline hover:text-white">{{ $snapshot->source_label }}</a>. Tabela archiwalna.</p>
                @endif
            </article>
        @endforeach

        @if ($leagueStandings->isEmpty() && $leagueTableSnapshots->isEmpty())
            @include('pages.partials.league-table-grid', ['rows' => collect()])
        @endif
    </div>
</section>

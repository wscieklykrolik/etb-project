@php
    $columns = collect([
        'points' => ['label' => 'Pkt', 'fields' => ['points']],
        'games' => ['label' => 'Mecze', 'fields' => ['games']],
        'record' => ['label' => 'Zw. - por.', 'fields' => ['wins', 'losses']],
        'home' => ['label' => 'Dom', 'fields' => ['home_wins', 'home_losses']],
        'away' => ['label' => 'Wyjazd', 'fields' => ['away_wins', 'away_losses']],
        'scores' => ['label' => 'Kosze', 'fields' => ['points_for', 'points_against']],
        'difference' => ['label' => 'Różnica (DIFF)', 'fields' => ['points_difference']],
        'ratio' => ['label' => 'Stosunek', 'fields' => ['ratio']],
        'form' => ['label' => 'Ostatnie 5 meczów', 'fields' => ['last_five']],
    ])->filter(fn ($column) => $rows->contains(fn ($row) => collect($column['fields'])->every(fn ($field) => $row->$field !== null && $row->$field !== [])));
@endphp
<div class="overflow-hidden rounded-lg border border-zinc-800 bg-zinc-950 shadow-xl">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-zinc-800 bg-zinc-900 text-xs uppercase tracking-wide text-zinc-400">
                <tr>
                    <th class="px-4 py-3">M</th>
                    <th class="px-4 py-3">Drużyna</th>
                    @foreach($columns as $column)
                        <th class="whitespace-nowrap px-4 py-3 text-center">{{ $column['label'] }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-800">
                @forelse ($rows as $standing)
                    @php
                        $teamName = $standing->opponent?->name ?? $standing->team_name;
                        $logoPath = $standing->opponent?->logo_path ?? $standing->logo_path;
                    @endphp
                    <tr @class(['transition hover:bg-zinc-900', 'bg-yellow-400/10' => str($teamName)->lower()->contains('etb')])>
                        <td class="px-4 py-4 text-lg font-black text-white">{{ $standing->position }}</td>
                        <td class="px-4 py-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded bg-white p-2">
                                    @if ($logoPath)
                                        <img src="{{ \App\Support\MediaStorage::url($logoPath) }}" alt="{{ $teamName }}" class="max-h-full max-w-full object-contain">
                                    @else
                                        <span class="text-[10px] font-black text-zinc-500">LOGO</span>
                                    @endif
                                </div>
                                <span class="whitespace-nowrap font-black text-white">{{ $teamName }}</span>
                            </div>
                        </td>
                        @foreach($columns as $key => $column)
                            <td class="whitespace-nowrap px-4 py-4 text-center text-zinc-300">
                                @if(! collect($column['fields'])->every(fn ($field) => $standing->$field !== null && $standing->$field !== []))
                                    <span aria-label="Brak danych">—</span>
                                @elseif($key === 'form')
                                    @include('pages.partials.league-team-form', ['results' => $standing->last_five])
                                @elseif($key === 'difference')
                                    <span class="font-bold {{ $standing->points_difference >= 0 ? 'text-emerald-400' : 'text-red-400' }}">{{ $standing->points_difference > 0 ? '+' : '' }}{{ $standing->points_difference }}</span>
                                @elseif($key === 'ratio')
                                    {{ number_format((float) $standing->ratio, 4) }}
                                @else
                                    {{ collect($column['fields'])->map(fn ($field) => $standing->$field)->implode(' - ') }}
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ 2 + $columns->count() }}" class="px-4 py-8 text-center text-zinc-400">Tabela nie została jeszcze opublikowana</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

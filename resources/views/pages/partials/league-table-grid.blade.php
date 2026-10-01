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
                                        <span class="text-xs font-black text-zinc-500">LOGO</span>
                                    @endif
                                </div>
                                <span class="font-black text-white">{{ $teamName }}</span>
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
                        <td colspan="10" class="px-4 py-8 text-center text-zinc-400">Tabela nie została jeszcze opublikowana</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

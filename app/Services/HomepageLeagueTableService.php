<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\LeagueStanding;
use App\Models\LeagueTableSnapshot;
use Illuminate\Support\Collection;

class HomepageLeagueTableService
{
    public const AUTOMATIC_SOURCE = 'automatic';

    public const SETTING_KEY = 'homepage_league_table_source';

    /**
     * @return Collection<int, array{key: string, label: string, updated_at: mixed}>
     */
    public function availableTables(): Collection
    {
        $currentTables = collect(LzkoszLeagueTableService::SOURCES)
            ->map(function (array $configuration, string $source): ?array {
                $season = $this->seasonForSource($source, $configuration);
                $updatedAt = LeagueStanding::query()
                    ->where('league_id', $configuration['league_id'])
                    ->where('season', $season)
                    ->max('synced_at');

                if (! $updatedAt) {
                    return null;
                }

                return [
                    'key' => "current:{$source}",
                    'label' => "{$configuration['label']} — sezon {$season}",
                    'updated_at' => $updatedAt,
                ];
            })
            ->filter();

        $snapshots = LeagueTableSnapshot::query()
            ->where('is_published', true)
            ->whereHas('rows')
            ->get()
            ->map(fn (LeagueTableSnapshot $snapshot): array => [
                'key' => "snapshot:{$snapshot->id}",
                'label' => $snapshot->title.($snapshot->season ? " — sezon {$snapshot->season}" : ''),
                'updated_at' => $snapshot->updated_at,
            ]);

        return $currentTables
            ->concat($snapshots)
            ->sortByDesc('updated_at')
            ->values();
    }

    /**
     * @return array{title: string, rows: Collection<int, array<string, mixed>>}|null
     */
    public function preview(): ?array
    {
        $tables = $this->availableTables();

        if ($tables->isEmpty()) {
            return null;
        }

        $selectedKey = AppSetting::getValue(self::SETTING_KEY, self::AUTOMATIC_SOURCE);
        $table = $selectedKey === self::AUTOMATIC_SOURCE
            ? $tables->first()
            : $tables->firstWhere('key', $selectedKey);

        $table ??= $tables->first();
        $rows = $this->rowsFor((string) $table['key']);
        $ourIndex = $rows->search(fn (array $row): bool => $row['is_ours']);

        if ($ourIndex === false) {
            return null;
        }

        $start = match (true) {
            $ourIndex === 0 => 0,
            $ourIndex === $rows->count() - 1 => max(0, $rows->count() - 3),
            default => $ourIndex - 1,
        };

        return [
            'title' => (string) $table['label'],
            'rows' => $rows->slice($start, 3)->values(),
        ];
    }

    public function selection(): string
    {
        return AppSetting::getValue(self::SETTING_KEY, self::AUTOMATIC_SOURCE) ?: self::AUTOMATIC_SOURCE;
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function rowsFor(string $key): Collection
    {
        if (str_starts_with($key, 'snapshot:')) {
            $snapshot = LeagueTableSnapshot::query()
                ->where('is_published', true)
                ->with('rows')
                ->find((int) str($key)->after('snapshot:')->toString());

            return $snapshot?->rows->map(fn ($row): array => $this->normaliseRow(
                $row->position,
                $row->team_name,
                $row->logo_path,
                $row->points,
                $row->games,
                $row->wins,
                $row->losses,
            )) ?? collect();
        }

        $source = str($key)->after('current:')->toString();
        $configuration = LzkoszLeagueTableService::SOURCES[$source] ?? null;

        if (! $configuration) {
            return collect();
        }

        return LeagueStanding::query()
            ->with('opponent')
            ->where('league_id', $configuration['league_id'])
            ->where('season', $this->seasonForSource($source, $configuration))
            ->orderBy('position')
            ->get()
            ->map(fn (LeagueStanding $row): array => $this->normaliseRow(
                $row->position,
                $row->opponent->name,
                $row->opponent->logo_path,
                $row->points,
                $row->games,
                $row->wins,
                $row->losses,
            ));
    }

    /**
     * @return array<string, mixed>
     */
    private function normaliseRow(int $position, string $teamName, ?string $logoPath, int $points, int $games, int $wins, int $losses): array
    {
        return [
            'position' => $position,
            'team_name' => $teamName,
            'logo_path' => $logoPath,
            'points' => $points,
            'games' => $games,
            'wins' => $wins,
            'losses' => $losses,
            'is_ours' => str($teamName)->lower()->contains('etb'),
        ];
    }

    /**
     * @param  array<string, mixed>  $configuration
     */
    private function seasonForSource(string $source, array $configuration): string
    {
        return AppSetting::getValue("league_table_season_{$source}")
            ?: (AppSetting::getValue('league_table_source') === $source ? AppSetting::getValue('league_table_season') : null)
            ?: $configuration['season'];
    }
}

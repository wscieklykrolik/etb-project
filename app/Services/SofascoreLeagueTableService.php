<?php

namespace App\Services;

use App\Exceptions\LeagueTableSourceException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SofascoreLeagueTableService
{
    public const API_URL = 'https://www.sofascore.com/api/v1/unique-tournament/39654/season/107625';

    public function fetch(): Collection
    {
        $rows = $this->parse($this->get('/standings/total'));
        $events = collect();

        // Fetch older pages until each team has five finished games, or the season ends.
        for ($page = 0; $page < 20; $page++) {
            $data = $this->get('/events/last/'.$page);
            if (! isset($data['events']) || ! is_array($data['events'])) {
                throw new LeagueTableSourceException('Sofascore nie zwrócił poprawnej listy ostatnich meczów. Zachowano poprzednią tabelę.');
            }
            $events = $events->concat($data['events'])->unique('id')->values();
            $rowsWithForm = $this->withForm($rows, $events);
            if (! ($data['hasNextPage'] ?? false) || $rowsWithForm->every(fn ($row) => count($row['last_five']) >= min(5, $row['games'] ?? 5))) {
                return $rowsWithForm;
            }
        }

        throw new LeagueTableSourceException('Nie udało się pobrać pełnej formy drużyn z Sofascore. Zachowano poprzednią tabelę.');
    }

    public function parse(array $data): Collection
    {
        $tables = $data['standings'] ?? [];
        if (! is_array($tables) || count($tables) !== 1 || ! is_array($tables[0]['rows'] ?? null) || $tables[0]['rows'] === []) {
            throw new LeagueTableSourceException('Sofascore nie zwrócił jednej, kompletnej tabeli ligi. Zachowano poprzednią tabelę.');
        }

        $rows = collect($tables[0]['rows'])->map(function ($row): array {
            if (! is_array($row) || ! is_numeric($row['team']['id'] ?? null) || ! is_string($row['team']['name'] ?? null) || trim($row['team']['name']) === '' || ! is_numeric($row['position'] ?? null)) {
                throw new LeagueTableSourceException('Tabela Sofascore zawiera niepoprawne dane drużyny. Zachowano poprzednią tabelę.');
            }

            foreach (['points', 'matches', 'wins', 'losses', 'scoresFor', 'scoresAgainst', 'scoreDiff'] as $field) {
                if (isset($row[$field]) && filter_var($row[$field], FILTER_VALIDATE_INT) === false) {
                    throw new LeagueTableSourceException('Tabela Sofascore zawiera niepoprawną statystykę. Zachowano poprzednią tabelę.');
                }
            }
            $number = fn ($key) => isset($row[$key]) ? (int) $row[$key] : null;
            $for = $number('scoresFor');
            $against = $number('scoresAgainst');

            return [
                'source_team_id' => (int) $row['team']['id'],
                'position' => (int) $row['position'],
                'team_name' => trim($row['team']['name']),
                'team_url' => 'https://www.sofascore.com/pl/basketball/team/'.Str::slug($row['team']['name']).'/'.$row['team']['id'],
                'points' => $number('points'),
                'games' => $number('matches'),
                'wins' => $number('wins'),
                'losses' => $number('losses'),
                'home_wins' => null,
                'home_losses' => null,
                'away_wins' => null,
                'away_losses' => null,
                'points_for' => $for,
                'points_against' => $against,
                'points_difference' => $number('scoreDiff') ?? ($for !== null && $against !== null ? $for - $against : null),
                'ratio' => null,
                'last_five' => [],
            ];
        });
        if ($rows->pluck('source_team_id')->unique()->count() !== $rows->count() || $rows->pluck('position')->unique()->count() !== $rows->count() || $rows->contains(fn ($row) => $row['position'] < 1)) {
            throw new LeagueTableSourceException('Tabela Sofascore zawiera powtórzone drużyny lub niepoprawne pozycje. Zachowano poprzednią tabelę.');
        }

        return $rows;
    }

    public function withForm(Collection $rows, Collection $events): Collection
    {
        return $rows->map(function (array $row) use ($events): array {
            $teamId = $row['source_team_id'];
            $row['last_five'] = $events
                ->filter(fn ($event) => ($event['status']['type'] ?? null) === 'finished'
                    && in_array($event['winnerCode'] ?? null, [1, 2], true)
                    && (int) ($event['season']['id'] ?? 0) === 107625
                    && (int) ($event['tournament']['uniqueTournament']['id'] ?? 0) === 39654
                    && in_array($teamId, [(int) ($event['homeTeam']['id'] ?? 0), (int) ($event['awayTeam']['id'] ?? 0)], true))
                ->sortByDesc('startTimestamp')->take(5)->reverse()
                ->map(function ($event) use ($teamId): array {
                    $home = (int) $event['homeTeam']['id'] === $teamId;

                    return [
                        'result' => ($home ? 1 : 2) === $event['winnerCode'] ? 'W' : 'L',
                        'opponent' => ($home ? $event['awayTeam']['name'] : $event['homeTeam']['name']) ?? '',
                    ];
                })->values()->all();

            return $row;
        });
    }

    private function get(string $path): array
    {
        $response = Http::acceptJson()->connectTimeout(10)->timeout(20)->get(self::API_URL.$path);
        if (in_array($response->status(), [403, 429], true)) {
            throw new LeagueTableSourceException('Sofascore odmówił automatycznego pobierania danych (HTTP '.$response->status().'). Poprzednia tabela i aktywne źródło pozostają bez zmian.');
        }
        if (! $response->successful() || ! is_array($response->json())) {
            throw new LeagueTableSourceException('Nie udało się pobrać danych z Sofascore. Spróbuj ponownie później.');
        }

        return $response->json();
    }
}

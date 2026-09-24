<?php

namespace App\Services;

use App\Models\LeagueStanding;
use App\Models\Opponent;
use DOMDocument;
use DOMXPath;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class LzkoszLeagueTableService
{
    public const LEAGUE_ID = 215;

    public const SEASON = '2025/2026';

    public const SOURCE_URL = 'https://lzkosz.pl/liga/215/tabela.html';

    public function sync(): int
    {
        $response = Http::timeout(15)->get(self::SOURCE_URL);

        if (! $response->successful()) {
            throw new RuntimeException('Nie udało się pobrać tabeli ŁZKosz.');
        }

        return $this->storeRows($this->parse($response->body()));
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function parse(string $html): Collection
    {
        $document = new DOMDocument;

        libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="utf-8" ?>'.$html);
        libxml_clear_errors();

        $xpath = new DOMXPath($document);

        foreach ($xpath->query('//table') as $table) {
            $header = Str::of($table->textContent)->squish()->lower()->toString();

            if (! str_contains($header, 'drużyna') || ! str_contains($header, 'stosunek')) {
                continue;
            }

            $rows = collect();

            foreach ($xpath->query('.//tbody/tr', $table) as $tr) {
                $cells = $xpath->query('./td', $tr);

                if ($cells->length < 10) {
                    continue;
                }

                $teamLink = $xpath->query('./td[2]//a', $tr)->item(0);
                $teamName = $this->cellText($cells->item(1));

                $rows->push([
                    'position' => (int) $this->cellText($cells->item(0)),
                    'team_name' => $teamName,
                    'team_url' => $teamLink?->attributes?->getNamedItem('href')?->nodeValue
                        ? $this->absoluteUrl($teamLink->attributes->getNamedItem('href')->nodeValue)
                        : null,
                    'points' => (int) $this->cellText($cells->item(2)),
                    'games' => (int) $this->cellText($cells->item(3)),
                    ...$this->splitRecord($this->cellText($cells->item(4)), 'wins', 'losses'),
                    ...$this->splitRecord($this->cellText($cells->item(5)), 'home_wins', 'home_losses'),
                    ...$this->splitRecord($this->cellText($cells->item(6)), 'away_wins', 'away_losses'),
                    ...$this->splitRecord($this->cellText($cells->item(7)), 'points_for', 'points_against'),
                    'points_difference' => (int) str_replace('+', '', $this->cellText($cells->item(8))),
                    'ratio' => (float) str_replace(',', '.', $this->cellText($cells->item(9))),
                ]);
            }

            if ($rows->isNotEmpty()) {
                return $rows;
            }
        }

        throw new RuntimeException('Nie znaleziono tabeli ligowej w odpowiedzi ŁZKosz.');
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     */
    private function storeRows(Collection $rows): int
    {
        $syncedAt = now();

        foreach ($rows as $row) {
            $opponent = Opponent::query()->firstOrCreate(
                ['name' => $row['team_name']],
                ['is_league_team' => true, 'source_team_url' => $row['team_url']]
            );

            $opponent->forceFill([
                'is_league_team' => true,
                'source_team_url' => $row['team_url'] ?? $opponent->source_team_url,
            ])->save();

            LeagueStanding::query()->updateOrCreate(
                [
                    'league_id' => self::LEAGUE_ID,
                    'season' => self::SEASON,
                    'opponent_id' => $opponent->id,
                ],
                [
                    'position' => $row['position'],
                    'points' => $row['points'],
                    'games' => $row['games'],
                    'wins' => $row['wins'],
                    'losses' => $row['losses'],
                    'home_wins' => $row['home_wins'],
                    'home_losses' => $row['home_losses'],
                    'away_wins' => $row['away_wins'],
                    'away_losses' => $row['away_losses'],
                    'points_for' => $row['points_for'],
                    'points_against' => $row['points_against'],
                    'points_difference' => $row['points_difference'],
                    'ratio' => $row['ratio'],
                    'source_team_name' => $row['team_name'],
                    'source_team_url' => $row['team_url'],
                    'synced_at' => $syncedAt,
                ]
            );
        }

        return $rows->count();
    }

    /**
     * @return array<string, int>
     */
    private function splitRecord(string $value, string $leftKey, string $rightKey): array
    {
        [$left, $right] = array_pad(preg_split('/\s*-\s*/', $value) ?: [], 2, 0);

        return [
            $leftKey => (int) $left,
            $rightKey => (int) $right,
        ];
    }

    private function cellText(?\DOMNode $cell): string
    {
        return trim(preg_replace('/\s+/u', ' ', $cell?->textContent ?? ''));
    }

    private function absoluteUrl(string $url): string
    {
        if (str_starts_with($url, 'http')) {
            return $url;
        }

        return 'https://lzkosz.pl/'.ltrim($url, '/');
    }
}

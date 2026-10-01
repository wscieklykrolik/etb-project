<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\LeagueStanding;
use App\Models\LeagueTableSnapshot;
use App\Models\Opponent;
use App\Services\LzkoszLeagueTableService;
use App\Support\MediaStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class LeagueTableController extends Controller
{
    public function sync(Request $request, LzkoszLeagueTableService $leagueTableService): RedirectResponse
    {
        $validated = $request->validate([
            'source' => ['nullable', 'in:lzkosz,kpzkosz'],
            'season' => ['nullable', 'regex:/^20\d{2}\/(?:\d{2}|20\d{2})$/'],
        ], [
            'season.regex' => 'Sezon wpisz w formacie 20XX/XX albo 20XX/20XX.',
        ]);

        $source = $validated['source'] ?? 'lzkosz';
        $configuration = LzkoszLeagueTableService::SOURCES[$source];
        $season = ($validated['season'] ?? null) ?: $configuration['season'];

        try {
            $count = $leagueTableService->sync($source, $season);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('profile.edit')
                ->with('error', 'Nie udało się pobrać tabeli z wybranego serwisu. Spróbuj ponownie za chwilę.');
        }

        AppSetting::setValue('league_table_source', $source);
        AppSetting::setValue('league_table_season', $season);

        return redirect()
            ->route('profile.edit')
            ->with('success', "Tabela {$configuration['label']} została pobrana. Zaktualizowano {$count} drużyn.");
    }

    public function archive(Request $request): RedirectResponse
    {
        $validated = $this->validateSnapshot($request, false);
        $source = AppSetting::getValue('league_table_source') ?: 'lzkosz';
        $configuration = LzkoszLeagueTableService::SOURCES[$source] ?? LzkoszLeagueTableService::SOURCES['lzkosz'];
        $season = AppSetting::getValue('league_table_season') ?: $configuration['season'];
        $standings = LeagueStanding::query()
            ->with('opponent')
            ->where('league_id', $configuration['league_id'])
            ->where('season', $season)
            ->orderBy('position')
            ->get();

        if ($standings->isEmpty()) {
            return back()->with('error', 'Nie ma bieżącej tabeli, którą można zapisać w archiwum.');
        }

        DB::transaction(function () use ($validated, $standings, $season): void {
            $snapshot = LeagueTableSnapshot::query()->create([
                'title' => $validated['title'],
                'season' => $validated['season'] ?: $season,
                'description' => $validated['description'] ?? null,
                'is_published' => $validated['is_published'] ?? false,
                'sort_order' => $validated['sort_order'] ?? 0,
            ]);

            foreach ($standings as $index => $standing) {
                $snapshot->rows()->create([
                    'position' => $standing->position,
                    'team_name' => $standing->opponent->name,
                    'logo_path' => $standing->opponent->logo_path,
                    'points' => $standing->points,
                    'games' => $standing->games,
                    'wins' => $standing->wins,
                    'losses' => $standing->losses,
                    'home_wins' => $standing->home_wins,
                    'home_losses' => $standing->home_losses,
                    'away_wins' => $standing->away_wins,
                    'away_losses' => $standing->away_losses,
                    'points_for' => $standing->points_for,
                    'points_against' => $standing->points_against,
                    'points_difference' => $standing->points_difference,
                    'ratio' => $standing->ratio,
                    'sort_order' => $index,
                ]);
            }
        });

        return back()->with('success', 'Tabela została zapisana jako niezależne archiwum sezonu.');
    }

    public function storeManual(Request $request): RedirectResponse
    {
        $validated = $this->validateSnapshot($request, true);
        $rows = $this->parseManualRows($validated['rows'] ?? '');

        DB::transaction(function () use ($validated, $rows): void {
            $snapshot = LeagueTableSnapshot::query()->create([
                'title' => $validated['title'],
                'season' => $validated['season'] ?? null,
                'description' => $validated['description'] ?? null,
                'is_published' => ($validated['is_published'] ?? false) && $rows !== [],
                'sort_order' => $validated['sort_order'] ?? 0,
            ]);
            $snapshot->rows()->createMany($rows);
        });

        return back()->with('success', 'Ręczna tabela została zapisana.');
    }

    public function updateManual(Request $request, LeagueTableSnapshot $snapshot): RedirectResponse
    {
        $validated = $this->validateSnapshot($request, true);
        $rows = $this->parseManualRows($validated['rows'] ?? '');

        DB::transaction(function () use ($snapshot, $validated, $rows): void {
            $snapshot->update([
                'title' => $validated['title'],
                'season' => $validated['season'] ?? null,
                'description' => $validated['description'] ?? null,
                'is_published' => ($validated['is_published'] ?? false) && $rows !== [],
                'sort_order' => $validated['sort_order'] ?? 0,
            ]);
            $snapshot->rows()->delete();
            $snapshot->rows()->createMany($rows);
        });

        return back()->with('success', 'Tabela została zaktualizowana.');
    }

    public function destroyManual(LeagueTableSnapshot $snapshot): RedirectResponse
    {
        $snapshot->delete();

        return back()->with('success', 'Tabela została usunięta.');
    }

    public function updateExternalLink(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'url' => ['nullable', 'url', 'max:2048'],
            'label' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        AppSetting::setValue('league_external_url', $validated['url'] ?? '');
        AppSetting::setValue('league_external_label', $validated['label'] ?? '');
        AppSetting::setValue('league_external_description', $validated['description'] ?? '');

        return back()->with('success', 'Dodatkowy odnośnik do ligi został zapisany.');
    }

    public function updateOpponent(Request $request, Opponent $opponent): RedirectResponse
    {
        $validated = $request->validate([
            'logo' => ['nullable', 'image', 'max:'.config('media.max_upload_kilobytes')],
        ]);

        if ($request->hasFile('logo')) {
            $oldPath = $opponent->logo_path;
            $opponent->logo_path = MediaStorage::store($request->file('logo'), 'team-logos');
            $opponent->save();
            MediaStorage::delete($oldPath);

            if (Str::of($opponent->name)->lower()->contains('etb')) {
                AppSetting::setValue('default_home_logo', $opponent->logo_path);
            }
        }

        return back()->with('success', 'Logo drużyny zostało zaktualizowane.');
    }

    private function validateSnapshot(Request $request, bool $withRows): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'season' => ['nullable', 'regex:/^20\d{2}\/(?:\d{2}|20\d{2})$/'],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_published' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'rows' => $withRows ? ['nullable', 'string'] : ['exclude'],
        ], [
            'season.regex' => 'Sezon wpisz w formacie 20XX/XX albo 20XX/20XX.',
        ]);
    }

    private function parseManualRows(string $value): array
    {
        $rows = [];
        $lines = preg_split('/\R/u', trim($value)) ?: [];

        foreach ($lines as $lineNumber => $line) {
            if (trim($line) === '') {
                continue;
            }

            $cells = array_map('trim', explode(';', $line));
            if (count($cells) !== 11) {
                throw ValidationException::withMessages([
                    'rows' => 'Wiersz '.($lineNumber + 1).' musi zawierać nazwę drużyny i 10 wartości oddzielonych średnikami.',
                ]);
            }

            [$teamName, $points, $games, $wins, $losses, $homeWins, $homeLosses, $awayWins, $awayLosses, $pointsFor, $pointsAgainst] = $cells;
            foreach (array_slice($cells, 1) as $cell) {
                if (filter_var($cell, FILTER_VALIDATE_INT) === false) {
                    throw ValidationException::withMessages(['rows' => 'Wiersz '.($lineNumber + 1).' zawiera wartość, która nie jest liczbą całkowitą.']);
                }
            }

            $opponent = Opponent::query()->whereRaw('LOWER(name) = ?', [Str::lower($teamName)])->first();
            $difference = (int) $pointsFor - (int) $pointsAgainst;
            $rows[] = [
                'position' => count($rows) + 1,
                'team_name' => $teamName,
                'logo_path' => $opponent?->logo_path,
                'points' => (int) $points,
                'games' => (int) $games,
                'wins' => (int) $wins,
                'losses' => (int) $losses,
                'home_wins' => (int) $homeWins,
                'home_losses' => (int) $homeLosses,
                'away_wins' => (int) $awayWins,
                'away_losses' => (int) $awayLosses,
                'points_for' => (int) $pointsFor,
                'points_against' => (int) $pointsAgainst,
                'points_difference' => $difference,
                'ratio' => (int) $pointsAgainst === 0 ? 0 : (int) $pointsFor / (int) $pointsAgainst,
                'sort_order' => count($rows),
            ];
        }

        return $rows;
    }
}

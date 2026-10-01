<?php

namespace App\Http\Controllers;

use App\Models\LeagueStanding;
use App\Models\LeagueTableSnapshot;
use App\Models\AppSetting;
use App\Models\TeamMatch;
use App\Models\ThreeXThreeTournament;
use App\Services\LzkoszLeagueTableService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicScheduleController extends Controller
{
    public function index(Request $request): View
    {
        $season = $request->string('season')->toString();
        $view = $request->string('view', 'all')->toString();
        $sort = $request->string('sort', 'asc')->toString() === 'desc' ? 'desc' : 'asc';

        $query = TeamMatch::query()
            ->with(['opponent', 'sportsHall'])
            ->where(function ($query): void {
                $query->whereNull('publish_at')->orWhere('publish_at', '<=', now());
            });

        if ($season !== '') {
            $query->where('season', $season);
        }

        $matches = $query->orderBy('match_date', $sort)->get();

        if ($view === TeamMatch::STATUS_UPCOMING) {
            $matches = $matches->filter(fn (TeamMatch $match): bool => $match->isUpcoming())->values();
        } elseif ($view === TeamMatch::STATUS_FINISHED) {
            $matches = $matches->filter(fn (TeamMatch $match): bool => $match->isFinished())->values();
        }

        $seasons = TeamMatch::query()
            ->whereNotNull('season')
            ->distinct()
            ->orderByDesc('season')
            ->pluck('season');

        $lzkoszMatches = TeamMatch::query()
            ->with(['opponent', 'sportsHall'])
            ->where('include_in_lzkosz', true)
            ->where(function ($query): void {
                $query->whereNull('publish_at')->orWhere('publish_at', '<=', now());
            })
            ->orderBy('match_date')
            ->get();

        $leagueStandings = $this->leagueStandings();

        $participatingUpcomingTournaments = ThreeXThreeTournament::query()
            ->with('categories')
            ->participating()
            ->upcoming()
            ->orderBy('date')
            ->get();
        $participatingFinishedTournaments = ThreeXThreeTournament::query()
            ->with('categories')
            ->participating()
            ->finished()
            ->orderByDesc('date')
            ->get();
        $organizedUpcomingTournaments = ThreeXThreeTournament::query()
            ->with('categories')
            ->organized()
            ->upcoming()
            ->orderBy('date')
            ->get();
        $organizedFinishedTournaments = ThreeXThreeTournament::query()
            ->with('categories')
            ->organized()
            ->finished()
            ->orderByDesc('date')
            ->get();

        return view('pages.schedule', [
            'matches' => $matches,
            'upcomingMatches' => $matches->filter(fn (TeamMatch $match): bool => $match->isUpcoming()),
            'finishedMatches' => $matches->filter(fn (TeamMatch $match): bool => $match->isFinished()),
            'seasons' => $seasons,
            'selectedSeason' => $season,
            'selectedView' => $view,
            'selectedSort' => $sort,
            'roundOneMatches' => $lzkoszMatches->where('lzkosz_round', TeamMatch::LZKOSZ_ROUND_ONE),
            'roundTwoMatches' => $lzkoszMatches->where('lzkosz_round', TeamMatch::LZKOSZ_ROUND_TWO),
            'leagueStandings' => $leagueStandings,
            'leagueTableSeason' => $this->leagueConfiguration()['season'],
            'leagueTableSourceLabel' => $this->leagueConfiguration()['label'],
            'leagueTableSnapshots' => $this->publishedLeagueTableSnapshots(),
            'participatingUpcomingTournaments' => $participatingUpcomingTournaments,
            'participatingFinishedTournaments' => $participatingFinishedTournaments,
            'organizedUpcomingTournaments' => $organizedUpcomingTournaments,
            'organizedFinishedTournaments' => $organizedFinishedTournaments,
        ]);
    }

    public function show(TeamMatch $match): View
    {
        abort_unless($match->isPublished(), 404);

        $match->load(['opponent', 'sportsHall']);

        return view('pages.schedule-show', compact('match'));
    }

    public function lzkosz(): View
    {
        $matches = TeamMatch::query()
            ->with(['opponent', 'sportsHall'])
            ->where('include_in_lzkosz', true)
            ->where(function ($query): void {
                $query->whereNull('publish_at')->orWhere('publish_at', '<=', now());
            })
            ->orderBy('match_date')
            ->get();

        return view('pages.schedule-lzkosz', [
            'roundOneMatches' => $matches->where('lzkosz_round', TeamMatch::LZKOSZ_ROUND_ONE),
            'roundTwoMatches' => $matches->where('lzkosz_round', TeamMatch::LZKOSZ_ROUND_TWO),
        ]);
    }

    public function table(): View
    {
        return view('pages.schedule-table', [
            'leagueStandings' => $this->leagueStandings(),
            'leagueTableSeason' => $this->leagueConfiguration()['season'],
            'leagueTableSourceLabel' => $this->leagueConfiguration()['label'],
            'leagueTableSnapshots' => $this->publishedLeagueTableSnapshots(),
        ]);
    }

    public function thirdLeague(): View
    {
        return view('pages.schedule-third-league', [
            'externalLeagueUrl' => AppSetting::getValue('league_external_url', 'https://www.kpzkosz.com/liga/89.html'),
            'externalLeagueLabel' => AppSetting::getValue('league_external_label', 'Otwórz ligę w KPZKosz'),
            'externalLeagueDescription' => AppSetting::getValue('league_external_description', 'W tym sezonie nasza tabela i oficjalna strona ligi są dostępne w serwisie KPZKosz.'),
        ]);
    }

    private function leagueStandings()
    {
        $configuration = $this->leagueConfiguration();

        return LeagueStanding::query()
            ->with('opponent')
            ->where('league_id', $configuration['league_id'])
            ->where('season', $configuration['season'])
            ->orderBy('position')
            ->get();
    }

    private function publishedLeagueTableSnapshots()
    {
        return LeagueTableSnapshot::query()
            ->with('rows')
            ->where('is_published', true)
            ->whereHas('rows')
            ->orderBy('sort_order')
            ->orderByDesc('season')
            ->get();
    }

    private function leagueConfiguration(): array
    {
        $source = AppSetting::getValue('league_table_source') ?: 'lzkosz';
        $configuration = LzkoszLeagueTableService::SOURCES[$source] ?? LzkoszLeagueTableService::SOURCES['lzkosz'];
        $configuration['season'] = AppSetting::getValue('league_table_season') ?: $configuration['season'];

        return $configuration;
    }
}

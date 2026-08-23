<?php

namespace App\Http\Controllers;

use App\Models\TeamMatch;
use App\Models\LeagueStanding;
use App\Models\ThreeXThreeTournament;
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

        if (in_array($view, [TeamMatch::STATUS_UPCOMING, TeamMatch::STATUS_FINISHED], true)) {
            $query->where('status', $view);
        }

        $matches = $query->orderBy('match_date', $sort)->get();

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
            'upcomingMatches' => $matches->where('status', TeamMatch::STATUS_UPCOMING),
            'finishedMatches' => $matches->where('status', TeamMatch::STATUS_FINISHED),
            'seasons' => $seasons,
            'selectedSeason' => $season,
            'selectedView' => $view,
            'selectedSort' => $sort,
            'roundOneMatches' => $lzkoszMatches->where('lzkosz_round', TeamMatch::LZKOSZ_ROUND_ONE),
            'roundTwoMatches' => $lzkoszMatches->where('lzkosz_round', TeamMatch::LZKOSZ_ROUND_TWO),
            'leagueStandings' => $leagueStandings,
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
        ]);
    }

    private function leagueStandings()
    {
        return LeagueStanding::query()
            ->with('opponent')
            ->orderBy('position')
            ->get();
    }
}

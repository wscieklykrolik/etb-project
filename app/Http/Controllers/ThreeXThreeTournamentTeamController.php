<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreThreeXThreeTournamentTeamRequest;
use App\Models\ThreeXThreeTournament;
use App\Models\ThreeXThreeTournamentTeam;
use App\Services\ThreeXThreeTournamentFlowService;
use App\Services\ThreeXThreeTournamentTeamService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ThreeXThreeTournamentTeamController extends Controller
{
    public function __construct(private readonly ThreeXThreeTournamentTeamService $teamService) {}

    public function store(StoreThreeXThreeTournamentTeamRequest $request, ThreeXThreeTournament $tournament): RedirectResponse
    {
        $this->teamService->register(
            $tournament,
            $request->user()->id,
            $request->validated(),
            $request->file('logo')
        );

        return redirect()
            ->route('three-x-three.tournaments.show', $tournament)
            ->with('success', 'Drużyna została zgłoszona do turnieju.');
    }

    public function show(ThreeXThreeTournamentTeam $team, ThreeXThreeTournamentFlowService $flowService): View
    {
        $team->load(['tournament', 'players']);

        return view('pages.schedule-3x3-team-show', [
            'team' => $team,
            'stats' => $flowService->tournamentTeamStats($team->name),
        ]);
    }
}

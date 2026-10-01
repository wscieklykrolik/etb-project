<?php

namespace App\Http\Controllers;

use App\Enums\BasketballPosition;
use App\Models\Player;
use App\Models\TeamStaff;
use App\Models\ThreeXThreeMember;
use Illuminate\View\View;

class PublicTeamController extends Controller
{
    public function index(): View
    {
        return view('pages.team', [
            ...$this->playersData(),
            'staff' => $this->staffMembers(),
            'members' => $this->threeXThreeMembers(),
        ]);
    }

    public function players(): View
    {
        return view('pages.team-players', $this->playersData());
    }

    public function player(Player $player): View
    {
        abort_unless($player->hasPublicDescription(), 404);

        return view('pages.team-player-show', compact('player'));
    }

    public function staff(): View
    {
        return view('pages.team-staff', [
            'staff' => $this->staffMembers(),
        ]);
    }

    public function threeXThree(): View
    {
        return view('pages.team-3x3-players', [
            'members' => $this->threeXThreeMembers(),
        ]);
    }

    private function playersData(): array
    {
        $players = Player::query()
            ->get()
            ->sortBy([
                fn (Player $a, Player $b): int => $a->positionOrder() <=> $b->positionOrder(),
                fn (Player $a, Player $b): int => $a->number <=> $b->number,
            ])
            ->groupBy('position');

        return [
            'playersByPosition' => $players,
            'positions' => BasketballPosition::cases(),
        ];
    }

    private function staffMembers()
    {
        return TeamStaff::query()->orderBy('sort_order')->orderBy('name')->get();
    }

    private function threeXThreeMembers()
    {
        return ThreeXThreeMember::query()->orderByDesc('is_coach')->orderBy('sort_order')->orderBy('name')->get();
    }
}

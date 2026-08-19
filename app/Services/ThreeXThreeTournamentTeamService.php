<?php

namespace App\Services;

use App\Models\ThreeXThreeTournament;
use App\Models\ThreeXThreeTournamentTeam;
use App\Support\MediaStorage;
use Illuminate\Http\UploadedFile;

class ThreeXThreeTournamentTeamService
{
    /**
     * @param array<string, mixed> $data
     */
    public function register(ThreeXThreeTournament $tournament, int $userId, array $data, ?UploadedFile $logo): ThreeXThreeTournamentTeam
    {
        $players = $data['players'];
        unset($data['players'], $data['logo']);

        if ($logo) {
            $data['logo_path'] = MediaStorage::store($logo, '3x3-team-logos');
        }

        $data['user_id'] = $userId;

        $team = $tournament->teams()->create($data);

        foreach (array_values($players) as $index => $player) {
            $team->players()->create([
                'name' => $player['name'],
                'sort_order' => $index,
            ]);
        }

        return $team;
    }

    public function delete(ThreeXThreeTournamentTeam $team): void
    {
        if ($team->logo_path) {
            MediaStorage::delete($team->logo_path);
        }

        $team->delete();
    }
}

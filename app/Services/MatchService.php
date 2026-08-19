<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\Opponent;
use App\Models\SportsHall;
use App\Models\TeamMatch;
use App\Support\MediaStorage;
use Illuminate\Http\UploadedFile;

class MatchService
{
    public function create(array $data, ?UploadedFile $opponentLogo, ?UploadedFile $homeLogo): TeamMatch
    {
        return TeamMatch::query()->create($this->prepareData($data, $opponentLogo, $homeLogo));
    }

    public function update(TeamMatch $match, array $data, ?UploadedFile $opponentLogo, ?UploadedFile $homeLogo): TeamMatch
    {
        $prepared = $this->prepareData($data, $opponentLogo, $homeLogo, $match);
        $deleteAfterUpdate = [];

        if ($opponentLogo && $match->opponent_logo) {
            $deleteAfterUpdate[] = $match->opponent_logo;
        }

        if ($homeLogo && $match->home_logo) {
            $deleteAfterUpdate[] = $match->home_logo;
        }

        $match->update($prepared);

        foreach (array_unique($deleteAfterUpdate) as $path) {
            MediaStorage::delete($path);
        }

        return $match;
    }

    public function delete(TeamMatch $match): void
    {
        if ($match->opponent_logo) {
            MediaStorage::delete($match->opponent_logo);
        }

        if ($match->home_logo) {
            MediaStorage::delete($match->home_logo);
        }

        $match->delete();
    }

    private function prepareData(
        array $data,
        ?UploadedFile $opponentLogo,
        ?UploadedFile $homeLogo,
        ?TeamMatch $match = null
    ): array {
        $status = $data['status'];
        $opponentName = trim((string) $data['opponent_name']);
        $locationName = trim((string) $data['location']);
        $opponent = $this->findOrCreateOpponent($opponentName);
        $sportsHall = $this->findOrCreateSportsHall($locationName);

        if ($opponentLogo) {
            $oldOpponentLogoPath = $opponent->logo_path;

            $opponent->update([
                'logo_path' => MediaStorage::store($opponentLogo, 'opponents'),
            ]);

            if ($oldOpponentLogoPath && ! TeamMatch::query()->where('opponent_logo', $oldOpponentLogoPath)->exists()) {
                MediaStorage::delete($oldOpponentLogoPath);
            }
        }

        unset($data['opponent'], $data['opponent_logo'], $data['home_logo']);

        if ($status === TeamMatch::STATUS_UPCOMING) {
            $data['our_score'] = null;
            $data['opponent_score'] = null;
        }

        if (! (bool) ($data['include_in_lzkosz'] ?? false)) {
            $data['lzkosz_round'] = null;
        }

        if (! (bool) ($data['is_ticketed'] ?? false)) {
            $data['ticket_url'] = null;
        }

        $data['opponent_name'] = $opponentName;
        $data['location'] = $locationName;
        $data['opponent_id'] = $opponent->id;
        $data['sports_hall_id'] = $sportsHall->id;
        $data['opponent_logo'] = $opponent->logo_path ?? $match?->opponent_logo;

        if ($homeLogo) {
            $data['home_logo'] = MediaStorage::store($homeLogo, 'team-logos');
        } elseif ($match) {
            $data['home_logo'] = $match->home_logo;
        } else {
            $data['home_logo'] = AppSetting::getValue('default_home_logo');
        }

        return $data;
    }

    private function findOrCreateOpponent(string $name): Opponent
    {
        return Opponent::query()->firstOrCreate([
            'name' => trim($name),
        ]);
    }

    private function findOrCreateSportsHall(string $name): SportsHall
    {
        return SportsHall::query()->firstOrCreate([
            'name' => trim($name),
        ]);
    }
}



<?php

namespace App\Services;

use App\Models\Player;
use App\Support\MediaStorage;
use Illuminate\Http\UploadedFile;

class PlayerService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, ?UploadedFile $photo): Player
    {
        $data['publish_description'] = (bool) ($data['publish_description'] ?? false);
        $data['is_starting_five'] = (bool) ($data['is_starting_five'] ?? false);

        if ($photo) {
            $data['photo_path'] = MediaStorage::store($photo, 'players');
        }

        return Player::query()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Player $player, array $data, ?UploadedFile $photo): Player
    {
        $data['publish_description'] = (bool) ($data['publish_description'] ?? false);
        $data['is_starting_five'] = (bool) ($data['is_starting_five'] ?? false);
        $oldPath = null;

        if ($photo) {
            $oldPath = $player->photo_path;
            $data['photo_path'] = MediaStorage::store($photo, 'players');
        }

        $player->update($data);
        MediaStorage::delete($oldPath);

        return $player;
    }

    public function delete(Player $player): void
    {
        if ($player->photo_path) {
            MediaStorage::delete($player->photo_path);
        }

        $player->delete();
    }
}

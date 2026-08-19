<?php

namespace App\Services;

use App\Models\Sponsor;
use App\Support\MediaStorage;
use Illuminate\Http\UploadedFile;

class SponsorService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, UploadedFile $logo): Sponsor
    {
        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['logo_path'] = MediaStorage::store($logo, 'sponsors');

        return Sponsor::query()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Sponsor $sponsor, array $data, ?UploadedFile $logo): Sponsor
    {
        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $oldPath = null;

        if ($logo) {
            $oldPath = $sponsor->logo_path;
            $data['logo_path'] = MediaStorage::store($logo, 'sponsors');
        }

        $sponsor->update($data);
        MediaStorage::delete($oldPath);

        return $sponsor;
    }

    public function delete(Sponsor $sponsor): void
    {
        MediaStorage::delete($sponsor->logo_path);
        $sponsor->delete();
    }
}

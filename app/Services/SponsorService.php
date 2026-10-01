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
    public function create(array $data, UploadedFile $logo, ?UploadedFile $homepageLogo): Sponsor
    {
        $useSameLogo = (bool) ($data['use_same_logo'] ?? true);
        unset($data['use_same_logo']);

        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['logo_path'] = MediaStorage::store($logo, 'sponsors');
        $data['homepage_logo_path'] = $useSameLogo || ! $homepageLogo
            ? null
            : MediaStorage::store($homepageLogo, 'sponsors');

        return Sponsor::query()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Sponsor $sponsor, array $data, ?UploadedFile $logo, ?UploadedFile $homepageLogo): Sponsor
    {
        $useSameLogo = (bool) ($data['use_same_logo'] ?? true);
        unset($data['use_same_logo']);

        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $oldPaths = [];

        if ($logo) {
            $oldPaths[] = $sponsor->logo_path;
            $data['logo_path'] = MediaStorage::store($logo, 'sponsors');
        }

        if ($useSameLogo) {
            $oldPaths[] = $sponsor->homepage_logo_path;
            $data['homepage_logo_path'] = null;
        } elseif ($homepageLogo) {
            $oldPaths[] = $sponsor->homepage_logo_path;
            $data['homepage_logo_path'] = MediaStorage::store($homepageLogo, 'sponsors');
        }

        $sponsor->update($data);

        foreach (array_unique(array_filter($oldPaths)) as $oldPath) {
            MediaStorage::delete($oldPath);
        }

        return $sponsor;
    }

    public function delete(Sponsor $sponsor): void
    {
        MediaStorage::delete($sponsor->logo_path);
        MediaStorage::delete($sponsor->homepage_logo_path);
        $sponsor->delete();
    }
}

<?php

namespace App\Services;

use App\Models\ClubSection;
use App\Support\MediaStorage;
use Illuminate\Http\UploadedFile;

class ClubSectionService
{
    /**
     * @param array<string, mixed> $data
     * @param array<int, UploadedFile> $photos
     */
    public function update(ClubSection $section, array $data, array $photos): ClubSection
    {
        $section->update([
            'body' => $data['body'] ?? null,
        ]);

        foreach ($photos as $photo) {
            $section->images()->create([
                'image_path' => MediaStorage::store($photo, "club/{$section->slug}"),
                'alt' => $section->title,
                'sort_order' => $section->images()->count(),
            ]);
        }

        return $section->refresh()->load('images');
    }
}

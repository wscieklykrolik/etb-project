<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClubSection;
use App\Services\ClubSectionService;
use App\Support\MediaStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ClubSectionController extends Controller
{
    public function update(Request $request, string $section, ClubSectionService $clubSectionService): RedirectResponse
    {
        $validated = $request->validate([
            'body' => ['nullable', 'string', 'max:50000'],
            'photos' => ['nullable', 'array', 'max:12'],
            'photos.*' => ['image', 'max:5120'],
        ]);

        ClubSection::syncDefaults();

        $clubSection = ClubSection::query()
            ->where('slug', $section)
            ->firstOrFail();

        $clubSectionService->update($clubSection, $validated, $request->file('photos', []));

        return redirect()
            ->route('profile.edit')
            ->with('success', "Sekcja „{$clubSection->title}” została zaktualizowana.");
    }

    public function destroyImage(ClubSection $section, int $image): RedirectResponse
    {
        $clubImage = $section->images()->whereKey($image)->firstOrFail();

        MediaStorage::delete($clubImage->image_path);
        $clubImage->delete();

        return redirect()
            ->route('profile.edit')
            ->with('success', 'Zdjęcie zostało usunięte.');
    }

    public function updateImage(Request $request, ClubSection $section, int $image): RedirectResponse
    {
        $validated = $request->validate([
            'caption' => ['nullable', 'string', 'max:1000'],
        ]);

        $clubImage = $section->images()->whereKey($image)->firstOrFail();
        $clubImage->update([
            'caption' => $validated['caption'] ?? null,
        ]);

        return redirect()
            ->route('profile.edit')
            ->with('success', 'Podpis zdjęcia został zaktualizowany.');
    }
}

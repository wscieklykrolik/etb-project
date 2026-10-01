<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClubSection;
use App\Models\AppSetting;
use App\Services\ClubSectionService;
use App\Support\MediaStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ClubSectionController extends Controller
{
    public function updateEmails(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'office_email' => ['required', 'email:rfc', 'max:254'],
            'marketing_email' => ['required', 'email:rfc', 'max:254'],
        ], [
            'office_email.required' => 'Podaj adres e-mail biura.',
            'office_email.email' => 'Podaj poprawny adres e-mail biura.',
            'marketing_email.required' => 'Podaj adres e-mail marketingu i mediów.',
            'marketing_email.email' => 'Podaj poprawny adres e-mail marketingu i mediów.',
        ]);

        AppSetting::setValue('office_email', $validated['office_email']);
        AppSetting::setValue('marketing_email', $validated['marketing_email']);

        return redirect()
            ->route('profile.edit', ['section' => 'contact'])
            ->with('success', 'Adresy e-mail zostały zaktualizowane.');
    }

    public function update(Request $request, string $section, ClubSectionService $clubSectionService): RedirectResponse
    {
        $validated = $request->validate([
            'body' => ['nullable', 'string', 'max:50000'],
            'photos' => ['nullable', 'array', 'max:12'],
            'photos.*' => ['image', 'max:'.config('media.max_upload_kilobytes')],
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

<?php

namespace App\Http\Controllers;

use App\Models\SponsorCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SponsorCategoryController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        SponsorCategory::query()->create($this->validatedData($request));

        return redirect()
            ->route('profile.edit', ['section' => 'sponsors'])
            ->with('success', 'Kategoria sponsorów została dodana.');
    }

    public function update(Request $request, SponsorCategory $sponsorCategory): RedirectResponse
    {
        $sponsorCategory->update($this->validatedData($request, $sponsorCategory));

        return redirect()
            ->route('profile.edit', ['section' => 'sponsors'])
            ->with('success', 'Kategoria sponsorów została zaktualizowana.');
    }

    public function destroy(SponsorCategory $sponsorCategory): RedirectResponse
    {
        if ($sponsorCategory->sponsors()->exists()) {
            return back()->with('error', 'Nie można usunąć kategorii, która zawiera sponsorów.');
        }

        $sponsorCategory->delete();

        return redirect()
            ->route('profile.edit', ['section' => 'sponsors'])
            ->with('success', 'Kategoria sponsorów została usunięta.');
    }

    /**
     * @return array{name: string, sort_order: int, is_active: bool}
     */
    private function validatedData(Request $request, ?SponsorCategory $category = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80', Rule::unique('sponsor_categories', 'name')->ignore($category)],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        return $data;
    }
}

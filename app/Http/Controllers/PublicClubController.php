<?php

namespace App\Http\Controllers;

use App\Models\ClubSection;
use App\Models\SponsorCategory;
use Illuminate\View\View;

class PublicClubController extends Controller
{
    public function index(): View
    {
        return view('pages.club', [
            'clubSections' => $this->sections(),
            'clubSponsorCategories' => $this->sponsorCategories(),
        ]);
    }

    public function show(string $section): View
    {
        $clubSection = $this->sections()->firstWhere('slug', $section);

        abort_unless($clubSection, 404);

        return view('pages.club-section', [
            'clubSection' => $clubSection,
            'clubSponsorCategories' => $this->sponsorCategories(),
        ]);
    }

    public function contact(): View
    {
        $clubSection = $this->sections()->firstWhere('slug', 'contact');

        abort_unless($clubSection, 404);

        return view('pages.contact', [
            'clubSection' => $clubSection,
        ]);
    }

    private function sections()
    {
        ClubSection::syncDefaults();

        return ClubSection::query()
            ->with('images')
            ->whereIn('slug', array_keys(ClubSection::SECTIONS))
            ->orderBy('sort_order')
            ->get();
    }

    private function sponsorCategories()
    {
        return SponsorCategory::query()
            ->active()
            ->whereHas('sponsors', fn ($query) => $query->active())
            ->with(['sponsors' => fn ($query) => $query
                ->active()
                ->orderBy('sort_order')
                ->orderBy('name')])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }
}

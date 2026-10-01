<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSponsorRequest;
use App\Http\Requests\UpdateSponsorRequest;
use App\Models\Sponsor;
use App\Services\AdminNotificationService;
use App\Services\SponsorService;
use Illuminate\Http\RedirectResponse;

class SponsorController extends Controller
{
    public function __construct(
        private readonly SponsorService $sponsorService,
        private readonly AdminNotificationService $notificationService
    ) {}

    public function store(StoreSponsorRequest $request): RedirectResponse
    {
        $sponsor = $this->sponsorService->create(
            $request->safe()->except(['logo', 'homepage_logo']),
            $request->file('logo'),
            $request->file('homepage_logo'),
        );
        $this->notificationService->record($request->user(), 'created', $sponsor, "Sponsor: {$sponsor->name}");

        return redirect()->route('profile.edit')->with('success', 'Sponsor został zapisany.');
    }

    public function update(UpdateSponsorRequest $request, Sponsor $sponsor): RedirectResponse
    {
        $this->sponsorService->update(
            $sponsor,
            $request->safe()->except(['logo', 'homepage_logo']),
            $request->file('logo'),
            $request->file('homepage_logo'),
        );
        $this->notificationService->record($request->user(), 'updated', $sponsor, "Sponsor: {$sponsor->name}");

        return redirect()->route('profile.edit')->with('success', 'Sponsor został zaktualizowany.');
    }

    public function destroy(Sponsor $sponsor): RedirectResponse
    {
        $label = "Sponsor: {$sponsor->name}";
        $id = $sponsor->id;
        $this->sponsorService->delete($sponsor);
        $this->notificationService->recordDeleted(request()->user(), Sponsor::class, $id, $label);

        return back()->with('success', 'Sponsor został usunięty.');
    }
}

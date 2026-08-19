<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\TeamMatch;
use App\Support\MediaStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminMatchController extends Controller
{
    public function create(): View
    {
        $defaultHomeLogo = AppSetting::getValue('default_home_logo');

        return view('admin.create-match', compact('defaultHomeLogo'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'opponent' => ['required', 'string', 'max:255'],
            'match_date' => ['required', 'date'],
            'location' => ['required', 'string', 'max:255'],
            'exact_address' => ['nullable', 'string', 'max:500'],
            'is_home' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'max:5120'],
            'away_logo' => ['nullable', 'image', 'max:5120'],
            'default_home_logo' => ['nullable', 'image', 'max:5120'],
        ]);

        $defaultHomeLogo = AppSetting::getValue('default_home_logo');

        if ($request->hasFile('default_home_logo')) {
            $defaultHomeLogo = MediaStorage::store($request->file('default_home_logo'), 'logos');
            AppSetting::setValue('default_home_logo', $defaultHomeLogo);
        }

        if ($request->hasFile('image')) {
            $validated['image'] = MediaStorage::store($request->file('image'), 'matches');
        }

        if ($request->hasFile('away_logo')) {
            $validated['away_logo'] = MediaStorage::store($request->file('away_logo'), 'logos');
        }

        $validated['home_logo'] = $defaultHomeLogo;
        $validated['is_home'] = $request->boolean('is_home');

        TeamMatch::create($validated);

        return redirect()->route('admin.matches.create')->with('status', 'Mecz został zapisany.');
    }
}

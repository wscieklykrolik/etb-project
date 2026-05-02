<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\MatchGame;
use App\Models\News;
use App\Models\Player;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        $matches = MatchGame::latest()->take(5)->get();
        $news = News::latest()->take(5)->get();
        $players = Player::latest()->take(5)->get();

        return view('profile.edit', [
            'user' => $user,
            'isAdmin' => $user->role === User::ROLE_ADMIN,
            'isEmployee' => $user->role === User::ROLE_EMPLOYEE,
            'isAthlete' => $user->role === User::ROLE_ATHLETE,
            'users' => $user->role === User::ROLE_ADMIN
                ? User::query()->orderBy('name')->get(['id', 'name', 'email', 'role'])
                : collect(),
            'athleteProfile' => $user->role === User::ROLE_ATHLETE
                ? $user->athleteProfile()->first()
                : null,
            'availableRoles' => User::roles(),
            'matches' => $matches,
            'news' => $news,
            'players' => $players,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}

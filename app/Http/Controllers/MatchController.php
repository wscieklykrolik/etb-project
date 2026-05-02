<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMatchRequest;
use App\Http\Requests\UpdateMatchRequest;
use App\Models\MatchGame;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MatchController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', MatchGame::class);
        $matches = MatchGame::where(function ($q) {
            $q->whereNull('publish_at')->orWhere('publish_at', '<=', now());
        })->latest()->get();
        return view('matches.index', compact('matches'));
    }
    public function show(MatchGame $gameMatch): View { $this->authorize('view', $gameMatch); return view('matches.show', ['match' => $gameMatch]); }
    public function create(): View { $this->authorize('create', MatchGame::class); return view('matches.create'); }
    public function store(StoreMatchRequest $request): RedirectResponse { MatchGame::query()->create($request->validated()); return back()->with('success', 'Changes saved successfully'); }
    public function edit(MatchGame $gameMatch): View { $this->authorize('update', $gameMatch); return view('matches.edit', ['match' => $gameMatch]); }
    public function update(UpdateMatchRequest $request, MatchGame $gameMatch): RedirectResponse { $gameMatch->update($request->validated()); return back()->with('success', 'Changes saved successfully'); }
    public function destroy(MatchGame $gameMatch): RedirectResponse { $this->authorize('delete', $gameMatch); $gameMatch->delete(); return back()->with('success', 'Changes saved successfully'); }
}

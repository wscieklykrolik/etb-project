<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMatchRequest;
use App\Http\Requests\UpdateMatchRequest;
use App\Models\Match;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MatchController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Match::class);

        $matches = Match::query()->orderBy('match_date')->get();

        return view('matches.index', compact('matches'));
    }

    public function show(Match $match): View
    {
        $this->authorize('view', $match);

        return view('matches.show', compact('match'));
    }

    public function create(): View
    {
        $this->authorize('create', Match::class);

        return view('matches.create');
    }

    public function store(StoreMatchRequest $request): RedirectResponse
    {
        $match = Match::query()->create($request->validated());

        return redirect()->route('matches.show', $match);
    }

    public function edit(Match $match): View
    {
        $this->authorize('update', $match);

        return view('matches.edit', compact('match'));
    }

    public function update(UpdateMatchRequest $request, Match $match): RedirectResponse
    {
        $match->update($request->validated());

        return redirect()->route('matches.show', $match);
    }

    public function destroy(Match $match): RedirectResponse
    {
        $this->authorize('delete', $match);

        $match->delete();

        return redirect()->route('matches.index');
    }
}

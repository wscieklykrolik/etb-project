<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePlayerRequest;
use App\Http\Requests\UpdatePlayerRequest;
use App\Models\Player;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PlayerController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Player::class);

        $players = Player::query()->orderBy('last_name')->orderBy('first_name')->get();

        return view('players.index', compact('players'));
    }

    public function create(): View
    {
        $this->authorize('create', Player::class);

        return view('players.create');
    }

    public function store(StorePlayerRequest $request): RedirectResponse
    {
        Player::query()->create($request->validated());

        return redirect()->route('players.index');
    }

    public function show(Player $player): View
    {
        $this->authorize('view', $player);

        return view('players.show', compact('player'));
    }

    public function edit(Player $player): View
    {
        $this->authorize('update', $player);

        return view('players.edit', compact('player'));
    }

    public function update(UpdatePlayerRequest $request, Player $player): RedirectResponse
    {
        $player->update($request->validated());

        return redirect()->route('players.index');
    }

    public function destroy(Player $player): RedirectResponse
    {
        $this->authorize('delete', $player);

        $player->delete();

        return redirect()->route('players.index');
    }
}

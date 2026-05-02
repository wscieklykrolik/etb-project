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
        $players = Player::query()->orderBy('first_name')->get();
        return view('players.index', compact('players'));
    }

    public function create(): View { $this->authorize('create', Player::class); return view('players.create'); }

    public function store(StorePlayerRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $parts = preg_split('/\s+/', trim($validated['name']), 2);
        Player::query()->create([
            'first_name' => $parts[0],
            'last_name' => $parts[1] ?? '-',
            'position' => $validated['position'],
            'number' => $validated['number'] ?? 0,
            'date_of_birth' => now()->subYears(18)->toDateString(),
        ]);
        return back()->with('success', 'Changes saved successfully');
    }

    public function show(Player $player): View { $this->authorize('view', $player); return view('players.show', compact('player')); }
    public function edit(Player $player): View { $this->authorize('update', $player); return view('players.edit', compact('player')); }
    public function update(UpdatePlayerRequest $request, Player $player): RedirectResponse { $player->update($request->validated()); return back()->with('success', 'Changes saved successfully'); }
    public function destroy(Player $player): RedirectResponse { $this->authorize('delete', $player); $player->delete(); return back()->with('success', 'Changes saved successfully'); }
}

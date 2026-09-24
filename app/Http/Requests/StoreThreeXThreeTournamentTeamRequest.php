<?php

namespace App\Http\Requests;

use App\Enums\ThreeXThreeCategory;
use App\Models\ThreeXThreeTournament;
use App\Rules\ProfanityFree;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreThreeXThreeTournamentTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        $tournament = $this->route('tournament');

        return $this->user() !== null
            && $tournament instanceof ThreeXThreeTournament
            && $tournament->acceptsInternalRegistrations();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $tournament = $this->route('tournament');
        $teamSize = $tournament instanceof ThreeXThreeTournament ? (int) $tournament->team_size : 3;
        $categories = $tournament instanceof ThreeXThreeTournament
            ? $tournament->categories()->pluck('category')->all()
            : ThreeXThreeCategory::values();

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                new ProfanityFree,
                Rule::unique('three_x_three_tournament_teams', 'name')
                    ->where('three_x_three_tournament_id', $tournament?->id),
            ],
            'category' => ['required', 'string', Rule::in($categories)],
            'logo' => ['nullable', 'image', 'max:2048'],
            'players' => ['required', 'array', 'size:'.$teamSize],
            'players.*.name' => ['required', 'string', 'max:255'],
        ];
    }
}

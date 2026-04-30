<?php

namespace App\Http\Requests;

use App\Models\Match;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        $match = $this->route('match');

        return $this->user()?->can('update', $match instanceof Match ? $match : Match::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'opponent' => ['required', 'string', 'max:255'],
            'match_date' => ['required', 'date'],
            'location' => ['required', 'string', 'max:255'],
            'result' => ['nullable', 'string', 'max:50'],
        ];
    }
}

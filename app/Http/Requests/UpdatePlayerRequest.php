<?php

namespace App\Http\Requests;

use App\Models\Player;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePlayerRequest extends FormRequest
{
    public function authorize(): bool
    {
        $player = $this->route('player');

        return $player instanceof Player
            ? ($this->user()?->can('update', $player) ?? false)
            : false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'number' => ['required', 'integer', 'min:0', 'max:99'],
            'position' => ['required', 'string'],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'height' => ['nullable', 'integer', 'min:100', 'max:250'],
            'weight' => ['nullable', 'integer', 'min:40', 'max:200'],
        ];
    }
}

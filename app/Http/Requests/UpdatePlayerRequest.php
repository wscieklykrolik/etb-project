<?php

namespace App\Http\Requests;

use App\Enums\BasketballPosition;
use App\Models\Player;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'position' => ['required', 'string', Rule::in(array_keys(BasketballPosition::options()))],
            'birth_year' => ['nullable', 'integer', 'min:1900', 'max:'.now()->year],
            'height' => ['nullable', 'integer', 'min:100', 'max:250'],
            'weight' => ['nullable', 'integer', 'min:40', 'max:200'],
            'description' => ['nullable', 'string', 'max:5000'],
            'publish_description' => ['nullable', 'boolean'],
            'is_starting_five' => ['nullable', 'boolean'],
            'photo' => ['nullable', 'image', 'max:4096'],
        ];
    }
}

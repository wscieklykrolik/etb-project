<?php

namespace App\Http\Requests;

use App\Models\Player;
use Illuminate\Foundation\Http\FormRequest;

class StorePlayerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Player::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'position' => ['required', 'string', 'max:255'],
            'number' => ['nullable', 'integer', 'min:0', 'max:99'],
        ];
    }
}

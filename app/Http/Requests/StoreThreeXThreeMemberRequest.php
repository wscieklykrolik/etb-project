<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class StoreThreeXThreeMemberRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['is_coach' => $this->boolean('is_coach')]);
    }

    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole([User::ROLE_ADMIN, User::ROLE_EMPLOYEE]) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'role' => ['required', 'string', 'max:255'],
            'birth_year' => ['nullable', 'integer', 'min:1900', 'max:'.now()->year],
            'description' => ['nullable', 'string', 'max:5000'],
            'is_coach' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'photo' => ['nullable', 'image', 'max:4096'],
        ];
    }
}

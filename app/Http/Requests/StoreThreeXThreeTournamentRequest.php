<?php

namespace App\Http\Requests;

use App\Enums\ThreeXThreeCategory;
use App\Models\ThreeXThreeTournament;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreThreeXThreeTournamentRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'registration_enabled' => $this->boolean('registration_enabled'),
            'type' => $this->input('type', ThreeXThreeTournament::TYPE_PARTICIPATING),
            'registration_mode' => $this->input('registration_mode', ThreeXThreeTournament::REGISTRATION_NONE),
        ]);
    }

    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole([User::ROLE_ADMIN, User::ROLE_EMPLOYEE]) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'location' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', 'string', Rule::in([ThreeXThreeTournament::STATUS_UPCOMING, ThreeXThreeTournament::STATUS_FINISHED])],
            'organizer' => ['nullable', 'string', 'max:255'],
            'type' => ['required', 'string', Rule::in([ThreeXThreeTournament::TYPE_PARTICIPATING, ThreeXThreeTournament::TYPE_ORGANIZED])],
            'registration_mode' => ['required', 'string', Rule::in([ThreeXThreeTournament::REGISTRATION_NONE, ThreeXThreeTournament::REGISTRATION_EXTERNAL, ThreeXThreeTournament::REGISTRATION_INTERNAL])],
            'registration_url' => ['nullable', 'required_if:registration_mode,'.ThreeXThreeTournament::REGISTRATION_EXTERNAL, 'url', 'max:2048'],
            'registration_enabled' => ['boolean'],
            'team_size' => ['nullable', 'required_if:registration_mode,'.ThreeXThreeTournament::REGISTRATION_INTERNAL, 'integer', 'min:2', 'max:12'],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['string', Rule::in(ThreeXThreeCategory::values())],
            'image' => ['nullable', 'image', 'max:'.config('media.max_upload_kilobytes')],
        ];
    }
}

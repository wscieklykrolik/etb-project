<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSponsorRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (! $this->has('use_same_logo')) {
            $this->merge(['use_same_logo' => '1']);
        }
    }

    public function authorize(): bool
    {
        return in_array($this->user()?->role, ['admin', 'employee'], true);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'sponsor_category_id' => ['required', 'exists:sponsor_categories,id'],
            'url' => ['required', 'url', 'max:2048'],
            'use_same_logo' => ['required', 'boolean'],
            'logo' => ['required', 'image', 'max:5120'],
            'homepage_logo' => ['exclude_if:use_same_logo,1', 'required', 'image', 'max:5120'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'homepage_logo.required' => 'Zdjęcie na stronę główną jest wymagane, gdy wybrano osobne zdjęcia.',
        ];
    }
}

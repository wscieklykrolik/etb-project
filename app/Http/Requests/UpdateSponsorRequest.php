<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSponsorRequest extends FormRequest
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
            'description' => ['nullable', 'string', 'max:5000'],
            'sponsor_category_id' => ['required', 'exists:sponsor_categories,id'],
            'url' => ['required', 'url', 'max:2048'],
            'use_same_logo' => ['required', 'boolean'],
            'logo' => ['nullable', 'image', 'max:'.config('media.max_upload_kilobytes')],
            'homepage_logo' => [
                'exclude_if:use_same_logo,1',
                Rule::requiredIf(fn (): bool => ! $this->boolean('use_same_logo') && ! $this->route('sponsor')?->homepage_logo_path),
                'nullable',
                'image',
                'max:'.config('media.max_upload_kilobytes'),
            ],
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

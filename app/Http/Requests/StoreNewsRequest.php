<?php

namespace App\Http\Requests;

use App\Models\News;
use Illuminate\Foundation\Http\FormRequest;

class StoreNewsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', News::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string', 'min:10'],
            'publish_at' => ['nullable', 'date'],
        ];
    }
}

<?php

namespace App\Http\Requests;

use App\Models\News;
use Illuminate\Foundation\Http\FormRequest;

class UpdateNewsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $news = $this->route('news');

        return $news instanceof News
            ? ($this->user()?->can('update', $news) ?? false)
            : false;
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

<?php

namespace App\Http\Requests;

use App\Models\News;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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
            'type' => ['required', Rule::in(News::types())],
            'content' => ['nullable', 'required_if:type,'.News::TYPE_ARTICLE, 'string', 'min:10'],
            'excerpt' => ['nullable', 'required_if:type,'.News::TYPE_GALLERY, 'required_if:type,'.News::TYPE_VIDEO, 'string', 'max:500'],
            'video_url' => ['nullable', 'required_if:type,'.News::TYPE_VIDEO, 'url', 'max:2048'],
            'article_author' => ['nullable', 'string', 'max:255'],
            'photo_author' => ['nullable', 'string', 'max:255'],
            'publish_at' => ['nullable', 'date'],
            'is_visible' => ['nullable', 'boolean'],
            'main_image' => ['nullable', 'image', 'max:5120'],
            'gallery' => ['nullable', 'array', 'max:100'],
            'gallery.*' => ['image', 'max:5120'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->input('type') !== News::TYPE_VIDEO || ! $this->filled('video_url')) {
                return;
            }

            $host = strtolower((string) parse_url((string) $this->input('video_url'), PHP_URL_HOST));

            if (! str_contains($host, 'youtube.com') && ! str_contains($host, 'youtu.be')) {
                $validator->errors()->add('video_url', 'Podaj link do filmu z YouTube.');
            }
        });
    }
}

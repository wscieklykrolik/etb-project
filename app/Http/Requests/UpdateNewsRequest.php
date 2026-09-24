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
        $draft = $this->boolean('save_as_draft');

        return [
            'save_as_draft' => ['sometimes', 'boolean'],
            'title' => [$draft ? 'nullable' : 'required', 'string', 'max:255'],
            'type' => ['required', Rule::in(News::types())],
            'content' => ['nullable', $draft ? 'sometimes' : 'required_if:type,'.News::TYPE_ARTICLE, 'string', 'min:'.($draft ? 0 : 10)],
            'excerpt' => ['nullable', $draft ? 'sometimes' : 'required_if:type,'.News::TYPE_GALLERY, $draft ? 'sometimes' : 'required_if:type,'.News::TYPE_VIDEO, 'string', 'max:500'],
            'video_url' => ['nullable', $draft ? 'sometimes' : 'required_if:type,'.News::TYPE_VIDEO, $draft ? 'string' : 'url', 'max:2048'],
            'article_author' => ['nullable', 'string', 'max:255'],
            'photo_author' => ['nullable', 'string', 'max:255'],
            'publish_at' => ['nullable', 'date'],
            'is_visible' => ['nullable', 'boolean'],
            'main_image' => ['nullable', 'image', 'max:5120'],
            'gallery' => ['nullable', 'array', 'max:100'],
            'gallery.*' => ['image', 'max:5120'],
            'remove_main_image' => ['sometimes', 'boolean'],
            'remove_images' => ['sometimes', 'array', 'max:100'],
            'remove_images.*' => ['integer', 'distinct', Rule::exists('news_images', 'id')->where('news_id', $this->route('news')?->id)],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $remaining = $this->route('news')->images()->whereNotIn('id', $this->input('remove_images', []))->count();
            $total = $remaining + count($this->file('gallery', []));
            if ($total > 100) {
                $validator->errors()->add('gallery', 'Galeria może zawierać maksymalnie 100 zdjęć.');
            }
            if (! $this->boolean('save_as_draft') && $this->input('type') === News::TYPE_GALLERY && $total === 0) {
                $validator->errors()->add('gallery', 'Dodaj przynajmniej jedno zdjęcie do galerii przed publikacją.');
            }

            if ($this->boolean('save_as_draft') || $this->input('type') !== News::TYPE_VIDEO || ! $this->filled('video_url')) {
                return;
            }

            $host = strtolower((string) parse_url((string) $this->input('video_url'), PHP_URL_HOST));

            if (! str_contains($host, 'youtube.com') && ! str_contains($host, 'youtu.be')) {
                $validator->errors()->add('video_url', 'Podaj link do filmu z YouTube.');
            }
        });
    }
}

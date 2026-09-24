<?php

namespace App\Services;

use App\Models\News;
use App\Support\MediaStorage;
use Illuminate\Http\UploadedFile;

class NewsService
{
    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, UploadedFile>  $gallery
     */
    public function create(array $data, int $authorId, ?UploadedFile $mainImage, array $gallery): News
    {
        $data = $this->normalizeData($data);

        if ($mainImage && $data['type'] === News::TYPE_ARTICLE) {
            $data['main_image_path'] = MediaStorage::store($mainImage, 'news/main');
        }

        $news = News::query()->create([
            ...$data,
            'author_id' => $authorId,
        ]);

        $this->storeGallery($news, $gallery);

        return $news;
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, UploadedFile>  $gallery
     */
    public function update(News $news, array $data, ?UploadedFile $mainImage, array $gallery): News
    {
        $removeImageIds = $data['remove_images'] ?? [];
        $removeMainImage = (bool) ($data['remove_main_image'] ?? false);
        unset($data['remove_images'], $data['remove_main_image']);
        $data = $this->normalizeData($data);
        $deleteAfterUpdate = [];

        if (($removeMainImage || $data['type'] !== News::TYPE_ARTICLE) && $news->main_image_path) {
            $deleteAfterUpdate[] = $news->main_image_path;
            $data['main_image_path'] = null;
        }

        if ($mainImage && $data['type'] === News::TYPE_ARTICLE) {
            $data['main_image_path'] = MediaStorage::store($mainImage, 'news/main');

            if ($news->main_image_path) {
                $deleteAfterUpdate[] = $news->main_image_path;
            }
        }

        $news->update($data);

        foreach ($news->images()->whereIn('id', $removeImageIds)->get() as $image) {
            $deleteAfterUpdate[] = $image->path;
            $image->delete();
        }
        $news->unsetRelation('images');

        foreach (array_unique($deleteAfterUpdate) as $path) {
            MediaStorage::delete($path);
        }

        $this->storeGallery($news, $gallery);

        return $news;
    }

    public function delete(News $news): void
    {
        if ($news->main_image_path) {
            MediaStorage::delete($news->main_image_path);
        }

        foreach ($news->images as $image) {
            MediaStorage::delete($image->path);
        }

        $news->delete();
    }

    public function publishNow(News $news): News
    {
        $rules = (new \App\Http\Requests\StoreNewsRequest)->rules();
        unset($rules['gallery'], $rules['gallery.*'], $rules['main_image']);
        \Illuminate\Support\Facades\Validator::make($news->getAttributes(), $rules)->validate();
        if ($news->type === News::TYPE_GALLERY && ! $news->images()->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'gallery' => 'Dodaj przynajmniej jedno zdjęcie do galerii przed publikacją.',
            ]);
        }
        if ($news->type === News::TYPE_VIDEO && ! $news->youtubeEmbedUrl()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'video_url' => 'Podaj link do filmu z YouTube.',
            ]);
        }

        $news->update([
            'is_draft' => false,
            'is_visible' => true,
            'publish_at' => now(),
        ]);

        return $news;
    }

    /**
     * @param  array<int, UploadedFile>  $gallery
     */
    private function storeGallery(News $news, array $gallery): void
    {
        $existingCount = $news->images()->count();
        $remainingSlots = max(0, 100 - $existingCount);
        $nextOrder = (int) $news->images()->max('sort_order') + 1;

        foreach (array_slice($gallery, 0, $remainingSlots) as $index => $image) {
            $news->images()->create([
                'path' => MediaStorage::store($image, 'news/gallery'),
                'sort_order' => $nextOrder + $index,
            ]);
        }
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function normalizeData(array $data): array
    {
        $data['is_draft'] = (bool) ($data['save_as_draft'] ?? false);
        unset($data['save_as_draft']);
        $data['is_visible'] = ! $data['is_draft'] && (bool) ($data['is_visible'] ?? false);
        $data['title'] = $data['title'] ?? '';
        $data['content'] = $data['content'] ?? '';
        $data['type'] = $data['type'] ?? News::TYPE_ARTICLE;

        if ($data['type'] !== News::TYPE_ARTICLE) {
            $data['content'] = $data['excerpt'] ?? $data['content'] ?? $data['title'];
        }

        if ($data['type'] !== News::TYPE_VIDEO) {
            $data['video_url'] = null;
        }

        if ($data['type'] !== News::TYPE_ARTICLE) {
            $data['article_author'] = null;
        }

        if (! in_array($data['type'], [News::TYPE_ARTICLE, News::TYPE_GALLERY], true)) {
            $data['photo_author'] = null;
        }

        return $data;
    }
}

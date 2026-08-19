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
        $data['is_visible'] = (bool) ($data['is_visible'] ?? true);

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
        $data = $this->normalizeData($data);
        $data['is_visible'] = (bool) ($data['is_visible'] ?? false);
        $deleteAfterUpdate = [];

        if ($data['type'] !== News::TYPE_ARTICLE && $news->main_image_path) {
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
        $news->update([
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

        foreach (array_slice($gallery, 0, $remainingSlots) as $index => $image) {
            $news->images()->create([
                'path' => MediaStorage::store($image, 'news/gallery'),
                'sort_order' => $existingCount + $index,
            ]);
        }
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function normalizeData(array $data): array
    {
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

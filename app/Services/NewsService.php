<?php

namespace App\Services;

use App\Models\News;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

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
            $data['main_image_path'] = $mainImage->store('news/main', 'public');
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

        if ($data['type'] !== News::TYPE_ARTICLE && $news->main_image_path) {
            Storage::disk('public')->delete($news->main_image_path);
            $data['main_image_path'] = null;
        }

        if ($mainImage && $data['type'] === News::TYPE_ARTICLE) {
            if ($news->main_image_path) {
                Storage::disk('public')->delete($news->main_image_path);
            }

            $data['main_image_path'] = $mainImage->store('news/main', 'public');
        }

        $news->update($data);
        $this->storeGallery($news, $gallery);

        return $news;
    }

    public function delete(News $news): void
    {
        if ($news->main_image_path) {
            Storage::disk('public')->delete($news->main_image_path);
        }

        foreach ($news->images as $image) {
            Storage::disk('public')->delete($image->path);
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
                'path' => $image->store('news/gallery', 'public'),
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

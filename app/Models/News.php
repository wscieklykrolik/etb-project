<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class News extends Model
{
    use HasFactory;

    public const TYPE_ARTICLE = 'article';

    public const TYPE_GALLERY = 'gallery';

    public const TYPE_VIDEO = 'video';

    protected $fillable = [
        'title',
        'type',
        'content',
        'excerpt',
        'video_url',
        'article_author',
        'photo_author',
        'author_id',
        'publish_at',
        'is_visible',
        'main_image_path',
    ];

    protected function casts(): array
    {
        return [
            'publish_at' => 'datetime',
            'is_visible' => 'boolean',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(NewsImage::class)->orderBy('sort_order');
    }

    /**
     * @return list<string>
     */
    public static function types(): array
    {
        return [
            self::TYPE_ARTICLE,
            self::TYPE_GALLERY,
            self::TYPE_VIDEO,
        ];
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            self::TYPE_GALLERY => 'Galeria zdjęć',
            self::TYPE_VIDEO => 'Wideo',
            default => 'Artykuł',
        };
    }

    public function previewImagePath(): ?string
    {
        if ($this->type === self::TYPE_GALLERY) {
            if ($this->relationLoaded('images')) {
                return $this->images->first()?->path;
            }

            return $this->images()->value('path');
        }

        if ($this->main_image_path) {
            return $this->main_image_path;
        }

        if ($this->relationLoaded('images')) {
            return $this->images->first()?->path;
        }

        return $this->images()->value('path');
    }

    public function youtubeEmbedUrl(): ?string
    {
        if ($this->type !== self::TYPE_VIDEO || ! $this->video_url) {
            return null;
        }

        $videoId = $this->youtubeVideoId();

        return $videoId ? "https://www.youtube.com/embed/{$videoId}" : null;
    }

    private function youtubeVideoId(): ?string
    {
        $parts = parse_url($this->video_url);

        if (! is_array($parts)) {
            return null;
        }

        $host = strtolower((string) ($parts['host'] ?? ''));
        $path = trim((string) ($parts['path'] ?? ''), '/');

        if (str_contains($host, 'youtu.be')) {
            $id = $path !== '' ? strtok($path, '/') : null;

            return is_string($id) ? $id : null;
        }

        if (str_contains($host, 'youtube.com')) {
            if (str_starts_with($path, 'embed/') || str_starts_with($path, 'shorts/')) {
                return explode('/', $path)[1] ?? null;
            }

            parse_str((string) ($parts['query'] ?? ''), $query);

            return isset($query['v']) && is_string($query['v']) ? $query['v'] : null;
        }

        return null;
    }

    public function isScheduled(): bool
    {
        return $this->publish_at !== null && $this->publish_at->isFuture();
    }

    public function isPubliclyVisible(): bool
    {
        return $this->is_visible && ($this->publish_at === null || $this->publish_at->isPast());
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_visible', true);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where(function (Builder $query): void {
            $query->whereNull('publish_at')->orWhere('publish_at', '<=', now());
        });
    }

    public function scopeScheduled(Builder $query): Builder
    {
        return $query->where('is_visible', true)->where('publish_at', '>', now());
    }
}

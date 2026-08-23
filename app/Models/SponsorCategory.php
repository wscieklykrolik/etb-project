<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SponsorCategory extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'legacy_type',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $category): void {
            if (! $category->slug) {
                $category->slug = self::uniqueSlug($category->name, $category->id);
            }
        });
    }

    /**
     * @return array<int, array{name: string, slug: string, legacy_type: string, sort_order: int}>
     */
    public static function defaultDefinitions(): array
    {
        return [
            ['name' => 'Partner strategiczny', 'slug' => Sponsor::TYPE_STRATEGIC, 'legacy_type' => Sponsor::TYPE_STRATEGIC, 'sort_order' => 10],
            ['name' => 'Sponsorzy', 'slug' => Sponsor::TYPE_SPONSOR, 'legacy_type' => Sponsor::TYPE_SPONSOR, 'sort_order' => 20],
            ['name' => 'Partnerzy', 'slug' => Sponsor::TYPE_PARTNER, 'legacy_type' => Sponsor::TYPE_PARTNER, 'sort_order' => 30],
            ['name' => 'Partner technologiczny', 'slug' => Sponsor::TYPE_TECHNOLOGY, 'legacy_type' => Sponsor::TYPE_TECHNOLOGY, 'sort_order' => 40],
        ];
    }

    public static function syncDefaults(): void
    {
        foreach (self::defaultDefinitions() as $definition) {
            $category = self::query()->firstOrCreate(
                ['slug' => $definition['slug']],
                [
                    'name' => $definition['name'],
                    'legacy_type' => $definition['legacy_type'],
                    'sort_order' => $definition['sort_order'],
                    'is_active' => true,
                ]
            );

            if (! $category->legacy_type) {
                $category->forceFill(['legacy_type' => $definition['legacy_type']])->save();
            }
        }
    }

    public function sponsors(): HasMany
    {
        return $this->hasMany(Sponsor::class);
    }

    public function typeKey(): string
    {
        return $this->legacy_type ?: $this->slug;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    private static function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'kategoria';
        $base = substr($base, 0, 40);
        $slug = $base;
        $counter = 2;

        while (self::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn (Builder $query) => $query->whereKeyNot($ignoreId))
            ->exists()) {
            $suffix = "-{$counter}";
            $slug = substr($base, 0, 40 - strlen($suffix)).$suffix;
            $counter++;
        }

        return $slug;
    }
}

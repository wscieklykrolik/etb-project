<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sponsor extends Model
{
    use HasFactory;

    public const TYPE_STRATEGIC = 'strategic';

    public const TYPE_PARTNER = 'partner';

    public const TYPE_SPONSOR = 'sponsor';

    public const TYPE_TECHNOLOGY = 'technology';

    protected $fillable = [
        'name',
        'type',
        'sponsor_category_id',
        'url',
        'logo_path',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $sponsor): void {
            if ($sponsor->sponsor_category_id) {
                $category = SponsorCategory::query()->find($sponsor->sponsor_category_id);

                if ($category) {
                    $sponsor->type = $category->typeKey();
                }

                return;
            }

            if ($sponsor->type) {
                $category = SponsorCategory::query()
                    ->where('legacy_type', $sponsor->type)
                    ->orWhere('slug', $sponsor->type)
                    ->first();

                if ($category) {
                    $sponsor->sponsor_category_id = $category->id;
                }
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public static function types(): array
    {
        return [
            self::TYPE_STRATEGIC => 'Partner strategiczny',
            self::TYPE_SPONSOR => 'Sponsorzy',
            self::TYPE_PARTNER => 'Partnerzy',
            self::TYPE_TECHNOLOGY => 'Partner technologiczny',
        ];
    }

    public function typeLabel(): string
    {
        return $this->category?->name ?? self::types()[$this->type] ?? $this->type;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(SponsorCategory::class, 'sponsor_category_id');
    }
}

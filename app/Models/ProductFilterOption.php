<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class ProductFilterOption extends Model
{
    protected $fillable = [
        'product_filter_group_id',
        'name',
        'slug',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $option): void {
            if (empty($option->slug)) {
                $option->slug = Str::slug($option->name);
            }
        });
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(ProductFilterGroup::class, 'product_filter_group_id');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_filter_option_product');
    }
}

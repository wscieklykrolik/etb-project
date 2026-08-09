<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price_grosze',
        'vat_rate',
        'category_id',
        'stock_qty',
        'is_physical',
        'is_published',
        'images',
    ];

    protected function casts(): array
    {
        return [
            'price_grosze' => 'integer',
            'vat_rate' => 'integer',
            'stock_qty' => 'integer',
            'is_physical' => 'boolean',
            'is_published' => 'boolean',
            'images' => 'array',
        ];
    }

    public function grossPriceGrosze(): int
    {
        return (int) round($this->price_grosze * (1 + $this->vat_rate / 100));
    }

    protected static function booted(): void
    {
        static::creating(function (self $product): void {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function variantSizes(): HasMany
    {
        return $this->hasMany(ProductVariantSize::class);
    }

    public function filterOptions(): BelongsToMany
    {
        return $this->belongsToMany(ProductFilterOption::class, 'product_filter_option_product');
    }

    public function displayPrice(): string
    {
        return number_format($this->price_grosze / 100, 2, ',', '').' zł';
    }
}

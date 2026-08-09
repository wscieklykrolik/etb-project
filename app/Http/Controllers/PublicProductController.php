<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductFilterGroup;
use App\Models\ProductFilterOption;
use App\Models\ProductVariantSize;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicProductController extends Controller
{
    public function index(Request $request): View
    {
        $selectedCategory = $request->integer('category') ?: null;
        $selectedSizes = collect((array) $request->query('sizes', []))
            ->filter()
            ->map(fn ($size) => (string) $size)
            ->values()
            ->all();
        $selectedFilterOptions = collect((array) $request->query('filter_options', []))
            ->map(fn ($option) => (int) $option)
            ->filter()
            ->values()
            ->all();
        $selectedAvailability = in_array($request->query('availability'), ['all', 'in_stock'], true)
            ? (string) $request->query('availability')
            : 'all';
        $selectedSort = in_array($request->query('sort'), ['newest', 'price_asc', 'price_desc', 'name'], true)
            ? (string) $request->query('sort')
            : 'newest';
        $minPrice = $request->filled('min_price') ? max(0, (int) round(((float) str_replace(',', '.', $request->query('min_price'))) * 100)) : null;
        $maxPrice = $request->filled('max_price') ? max(0, (int) round(((float) str_replace(',', '.', $request->query('max_price'))) * 100)) : null;

        $selectedOptionsByGroup = ProductFilterOption::query()
            ->whereIn('id', $selectedFilterOptions)
            ->get()
            ->groupBy('product_filter_group_id')
            ->map(fn ($options) => $options->pluck('id')->all());

        $products = Product::with(['category', 'variantSizes', 'filterOptions.group'])
            ->where('is_published', true)
            ->when($selectedCategory, fn ($query) => $query->where('category_id', $selectedCategory))
            ->when($selectedSizes, fn ($query) => $query->whereHas('variantSizes', fn ($variantQuery) => $variantQuery
                ->whereIn('size_label', $selectedSizes)
                ->where('stock_qty', '>', 0)))
            ->when($selectedAvailability === 'in_stock', fn ($query) => $query->where(function ($stockQuery): void {
                $stockQuery->where('stock_qty', '>', 0)
                    ->orWhereHas('variantSizes', fn ($variantQuery) => $variantQuery->where('stock_qty', '>', 0));
            }))
            ->when($minPrice !== null, fn ($query) => $query->where('price_grosze', '>=', $minPrice))
            ->when($maxPrice !== null, fn ($query) => $query->where('price_grosze', '<=', $maxPrice));

        foreach ($selectedOptionsByGroup as $optionIds) {
            $products->whereHas('filterOptions', fn ($query) => $query->whereIn('product_filter_options.id', $optionIds));
        }

        match ($selectedSort) {
            'price_asc' => $products->orderBy('price_grosze')->orderBy('name'),
            'price_desc' => $products->orderByDesc('price_grosze')->orderBy('name'),
            'name' => $products->orderBy('name'),
            default => $products->latest(),
        };

        $products = $products->paginate(12)->withQueryString();

        $categories = Category::whereHas('products', fn ($query) => $query->where('is_published', true))
            ->orderBy('name')
            ->get();

        $sizeOrder = ['XS' => 1, 'S' => 2, 'M' => 3, 'L' => 4, 'XL' => 5, 'XXL' => 6, '3XL' => 7];
        $availableSizes = ProductVariantSize::query()
            ->where('stock_qty', '>', 0)
            ->whereHas('product', fn ($query) => $query->where('is_published', true))
            ->distinct()
            ->pluck('size_label')
            ->sortBy(fn ($size) => $sizeOrder[strtoupper($size)] ?? 100)
            ->values();

        $filterGroups = ProductFilterGroup::query()
            ->where('is_active', true)
            ->with(['options' => fn ($query) => $query
                ->where('is_active', true)
                ->whereHas('products', fn ($productQuery) => $productQuery->where('is_published', true))
                ->withCount(['products' => fn ($productQuery) => $productQuery->where('is_published', true)])
                ->orderBy('sort_order')
                ->orderBy('name')])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->filter(fn ($group) => $group->options->isNotEmpty());

        return view('products.index', [
            'products' => $products,
            'categories' => $categories,
            'availableSizes' => $availableSizes,
            'filterGroups' => $filterGroups,
            'selectedCategory' => $selectedCategory,
            'selectedSizes' => $selectedSizes,
            'selectedFilterOptions' => $selectedFilterOptions,
            'selectedAvailability' => $selectedAvailability,
            'selectedSort' => $selectedSort,
        ]);
    }

    public function show(Product $product): View
    {
        abort_unless($product->is_published, 404);

        $product->load([
            'category',
            'filterOptions.group',
            'variantSizes' => fn ($query) => $query->orderBy('size_label'),
        ]);

        return view('products.show', compact('product'));
    }
}

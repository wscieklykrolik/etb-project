<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductFilterGroup;
use App\Models\ProductVariantSize;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Support\MediaStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        $products = Product::with(['category', 'filterOptions.group'])->latest()->paginate(20);

        return view('admin.products.index', compact('products'));
    }

    public function create(): View
    {
        $categories = Category::orderBy('name')->get();
        $filterGroups = ProductFilterGroup::query()
            ->with(['options' => fn ($query) => $query->orderBy('sort_order')->orderBy('name')])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.products.create', compact('categories', 'filterGroups'));
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $filterOptions = $validated['filter_options'] ?? [];
        unset($validated['images'], $validated['filter_options']);

        $product = Product::create($validated);
        $product->filterOptions()->sync($filterOptions);

        if ($request->hasFile('images')) {
            $paths = [];
            foreach ($request->file('images') as $image) {
                $paths[] = MediaStorage::store($image, 'products');
            }
            $product->update(['images' => $paths]);
        }

        return redirect()
            ->route('admin.products.edit', $product)
            ->with('success', 'Produkt został dodany.');
    }

    public function edit(Product $product): View
    {
        $categories = Category::orderBy('name')->get();
        $filterGroups = ProductFilterGroup::query()
            ->with(['options' => fn ($query) => $query->orderBy('sort_order')->orderBy('name')])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
        $variants = $product->variantSizes()->orderBy('size_label')->get();
        $selectedFilterOptions = $product->filterOptions()->pluck('product_filter_options.id')->all();

        return view('admin.products.edit', compact('product', 'categories', 'filterGroups', 'variants', 'selectedFilterOptions'));
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $validated = $request->validated();
        $filterOptions = $validated['filter_options'] ?? [];
        unset($validated['images'], $validated['filter_options']);

        $product->update($validated);
        $product->filterOptions()->sync($filterOptions);

        if ($request->hasFile('images')) {
            $paths = $product->images ?? [];
            foreach ($request->file('images') as $image) {
                $paths[] = MediaStorage::store($image, 'products');
            }
            $product->update(['images' => $paths]);
        }

        return redirect()
            ->route('admin.products.edit', $product)
            ->with('success', 'Produkt został zaktualizowany.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        foreach ($product->images ?? [] as $image) {
            MediaStorage::delete($image);
        }

        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Produkt został usunięty.');
    }

    public function addVariant(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'size_label' => ['required', 'string', 'max:50'],
            'stock_qty' => ['required', 'integer', 'min:0'],
            'extra_price_grosze' => ['required', 'integer', 'min:0'],
        ]);

        $product->variantSizes()->create($validated);

        return back()->with('success', 'Rozmiar został dodany.');
    }

    public function removeVariant(Product $product, ProductVariantSize $variant): RedirectResponse
    {
        $variant->delete();

        return back()->with('success', 'Rozmiar został usunięty.');
    }
}

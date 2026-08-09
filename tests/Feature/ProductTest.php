<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductFilterGroup;
use App\Models\ProductVariantSize;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $this->category = Category::factory()->create();
});

it('creates a product with factory', function () {
    $product = Product::factory()->create(['category_id' => $this->category->id]);

    $this->assertDatabaseHas('products', ['id' => $product->id]);
    expect($product->slug)->not->toBeEmpty();
});

it('auto-generates slug on create', function () {
    $product = Product::factory()->create([
        'name' => 'Test Product Name',
        'slug' => '',
        'category_id' => $this->category->id,
    ]);

    expect($product->slug)->toBe('test-product-name');
});

it('belongs to a category', function () {
    $product = Product::factory()->create(['category_id' => $this->category->id]);

    expect($product->category)->toBeInstanceOf(Category::class);
    expect($product->category->id)->toBe($this->category->id);
});

it('has variant sizes', function () {
    $product = Product::factory()->create(['category_id' => $this->category->id]);
    $variant = ProductVariantSize::factory()->create(['product_id' => $product->id]);

    expect($product->variantSizes)->toHaveCount(1);
    expect($variant->product->id)->toBe($product->id);
});

it('displays price in PLN format', function () {
    $product = Product::factory()->create([
        'price_grosze' => 2999,
        'category_id' => $this->category->id,
    ]);

    expect($product->displayPrice())->toBe('29,99 zł');
});

it('admin can create product via API', function () {
    $filterGroup = ProductFilterGroup::query()->create(['name' => 'Typ produktu', 'slug' => 'typ-produktu']);
    $filterOption = $filterGroup->options()->create(['name' => 'Koszulka', 'slug' => 'koszulka']);

    $response = $this->actingAs($this->admin)
        ->post(route('admin.products.store'), [
            'name' => 'New Product',
            'price_grosze' => 4999,
            'category_id' => $this->category->id,
            'stock_qty' => 10,
            'is_physical' => true,
            'is_published' => true,
            'filter_options' => [$filterOption->id],
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('products', ['name' => 'New Product']);
    expect(Product::where('name', 'New Product')->first()->filterOptions)->toHaveCount(1);
});

it('guest cannot access product admin', function () {
    $response = $this->get(route('admin.products.create'));
    $response->assertRedirect(route('login'));
});

it('admin can open shop management pages', function () {
    $filterGroup = ProductFilterGroup::query()->create(['name' => 'Typ produktu', 'slug' => 'typ-produktu']);
    $filterGroup->options()->create(['name' => 'Koszulka', 'slug' => 'koszulka']);

    $this->actingAs($this->admin)
        ->get(route('admin.product-filters.index'))
        ->assertOk()
        ->assertSee('Filtry sklepu')
        ->assertSee('Koszulka');

    $this->actingAs($this->admin)
        ->get(route('admin.products.create'))
        ->assertOk()
        ->assertSee('Etykiety i filtry produktu')
        ->assertSee('Koszulka');
});

it('shows public shop with published products', function () {
    $publishedProduct = Product::factory()->published()->create([
        'name' => 'Koszulka ETB',
        'category_id' => $this->category->id,
        'stock_qty' => 10,
    ]);
    $hiddenProduct = Product::factory()->create([
        'name' => 'Ukryty produkt',
        'category_id' => $this->category->id,
        'is_published' => false,
    ]);

    $response = $this->get(route('shop.index'));

    $response->assertOk();
    $response->assertSee($publishedProduct->name);
    $response->assertDontSee($hiddenProduct->name);
});

it('shows only published product details', function () {
    $publishedProduct = Product::factory()->published()->create([
        'name' => 'Bluza ETB',
        'category_id' => $this->category->id,
        'stock_qty' => 10,
    ]);
    $hiddenProduct = Product::factory()->create([
        'category_id' => $this->category->id,
        'is_published' => false,
    ]);

    $this->get(route('shop.show', $publishedProduct))
        ->assertOk()
        ->assertSee($publishedProduct->name);

    $this->get(route('shop.show', $hiddenProduct))
        ->assertNotFound();
});

it('filters public shop by product filter option and size', function () {
    $filterGroup = ProductFilterGroup::query()->create(['name' => 'Typ produktu', 'slug' => 'typ-produktu']);
    $shirtOption = $filterGroup->options()->create(['name' => 'Koszulka', 'slug' => 'koszulka']);
    $gadgetOption = $filterGroup->options()->create(['name' => 'Gadżet', 'slug' => 'gadzet']);

    $shirt = Product::factory()->published()->create([
        'name' => 'Koszulka filtrowana',
        'category_id' => $this->category->id,
        'stock_qty' => 10,
    ]);
    $shirt->filterOptions()->attach($shirtOption);
    ProductVariantSize::factory()->create(['product_id' => $shirt->id, 'size_label' => 'M', 'stock_qty' => 5]);

    $gadget = Product::factory()->published()->create([
        'name' => 'Kubek filtrowany',
        'category_id' => $this->category->id,
        'stock_qty' => 10,
    ]);
    $gadget->filterOptions()->attach($gadgetOption);
    ProductVariantSize::factory()->create(['product_id' => $gadget->id, 'size_label' => 'XL', 'stock_qty' => 5]);

    $this->get(route('shop.index', ['filter_options' => [$shirtOption->id], 'sizes' => ['M']]))
        ->assertOk()
        ->assertSee('Koszulka filtrowana')
        ->assertDontSee('Kubek filtrowany');
});

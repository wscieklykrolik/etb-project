<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductFilterGroup;
use App\Models\ProductVariantSize;
use App\Models\User;
use App\Support\MediaStorage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

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

it('stores product images larger than 25 MB on create and update', function () {
    config(['filesystems.media_disk' => 'public']);
    Storage::fake('public');
    $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII=');

    $data = [
        'name' => 'Produkt z dużymi zdjęciami',
        'price_grosze' => 4999,
        'stock_qty' => 10,
        'is_published' => true,
    ];

    $this->actingAs($this->admin)
        ->post(route('admin.products.store'), [
            ...$data,
            'images' => [UploadedFile::fake()->createWithContent('duze-zdjecie.png', $png.str_repeat("\0", 26 * 1024 * 1024))],
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    $product = Product::where('name', $data['name'])->firstOrFail();
    $firstImage = $product->images[0];
    Storage::disk('public')->assertExists($firstImage);

    $this->put(route('admin.products.update', $product), [
        ...$data,
        'images' => [UploadedFile::fake()->createWithContent('kolejne-duze-zdjecie.png', $png.str_repeat("\0", 27 * 1024 * 1024))],
    ])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    $images = $product->fresh()->images;
    expect($images)->toHaveCount(2);
    expect($images[0])->toBe($firstImage);
    Storage::disk('public')->assertExists($images);

    $this->get(route('shop.show', $product))
        ->assertOk()
        ->assertSee(MediaStorage::url($firstImage), false);

    $this->get(route('admin.products.edit', $product))
        ->assertOk()
        ->assertSee('data-max-upload-kb="0"', false)
        ->assertSee(MediaStorage::url($images[1]), false);
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

it('publishes or hides a product using the draft checkbox', function (bool $published) {
    $product = Product::factory()->create([
        'name' => 'Produkt ze zmienianą widocznością',
        'is_published' => ! $published,
    ]);

    $this->actingAs($this->admin)
        ->put(route('admin.products.update', $product), [
            'name' => $product->name,
            'price_grosze' => $product->price_grosze,
            'stock_qty' => $product->stock_qty,
            'is_published' => $published ? '1' : '0',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    expect($product->fresh()->is_published)->toBe($published);

    $form = $this->get(route('admin.products.edit', $product))
        ->assertOk()
        ->assertSee('Wersja robocza')
        ->assertSee('<input type="hidden" name="is_published" value="1">', false);

    preg_match('/<input type="checkbox" name="is_published" value="0"[^>]*>/', $form->getContent(), $checkbox);
    expect($checkbox)->toHaveCount(1);
    expect(str_contains($checkbox[0], 'checked'))->toBe(! $published);

    $shop = $this->get(route('shop.index'))->assertOk();
    if ($published) {
        $shop->assertSee($product->name);
        $this->get(route('shop.show', $product))->assertOk();
    } else {
        $shop->assertDontSee($product->name);
        $this->get(route('shop.show', $product))->assertNotFound();
    }
})->with([true, false]);

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

it('shows every product image and saves the gallery order with the cover first', function () {
    $images = ['products/przod.png', 'products/tyl.png', 'products/detal.png'];
    $product = Product::factory()->published()->create(['images' => $images]);

    $this->actingAs($this->admin)->put(route('admin.products.update', $product), [
        'name' => $product->name,
        'price_grosze' => $product->price_grosze,
        'stock_qty' => $product->stock_qty,
        'image_order' => [2, 0, 1],
    ])->assertSessionHasNoErrors()->assertRedirect();

    expect($product->fresh()->images)->toBe([$images[2], $images[0], $images[1]]);
    $this->get(route('shop.show', $product))->assertOk()
        ->assertSeeInOrder(array_map(fn ($path) => 'src="'.MediaStorage::url($path).'"', [$images[2], $images[0], $images[1]]), false)
        ->assertSee('Pokaż zdjęcie 3 produktu')
        ->assertSee('Następne zdjęcie');
    $this->get(route('admin.products.edit', $product))->assertOk()->assertSee('Zdjęcie główne');
    $this->get(route('shop.index'))->assertOk()->assertSee('src="'.MediaStorage::url($images[2]).'"', false);
});

it('rejects incomplete duplicate and foreign image positions without changing the gallery', function (array $order) {
    $images = ['products/pierwsze.png', 'products/drugie.png'];
    $product = Product::factory()->create(['images' => $images]);

    $this->actingAs($this->admin)->put(route('admin.products.update', $product), [
        'name' => $product->name,
        'price_grosze' => $product->price_grosze,
        'stock_qty' => $product->stock_qty,
        'image_order' => $order,
    ])->assertSessionHasErrors();

    expect($product->fresh()->images)->toBe($images);
})->with([[[]], [[0]], [[0, 0]], [[0, 99]], [['products/obce.png', 0]]]);

it('appends uploaded images after the reordered gallery', function () {
    config(['filesystems.media_disk' => 'public']);
    Storage::fake('public');
    $product = Product::factory()->create(['images' => ['products/przod.png', 'products/tyl.png']]);
    $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII=');

    $this->actingAs($this->admin)->put(route('admin.products.update', $product), [
        'name' => $product->name,
        'price_grosze' => $product->price_grosze,
        'stock_qty' => $product->stock_qty,
        'image_order' => [1, 0],
        'images' => [UploadedFile::fake()->createWithContent('detal.png', $png)],
    ])->assertSessionHasNoErrors()->assertRedirect();

    $images = $product->fresh()->images;
    expect($images)->toHaveCount(3);
    expect(array_slice($images, 0, 2))->toBe(['products/tyl.png', 'products/przod.png']);
    Storage::disk('public')->assertExists($images[2]);
});

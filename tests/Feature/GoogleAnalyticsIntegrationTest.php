<?php

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('does not render the google tag without a valid measurement id', function () {
    config(['analytics.google.measurement_id' => null]);

    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertDontSee('www.googletagmanager.com/gtag/js', false);
});

it('keeps google analytics blocked until analytics consent', function () {
    config([
        'analytics.google.measurement_id' => 'G-TEST12345',
        'analytics.google.debug' => false,
    ]);

    $response = $this->get(route('home'));
    $content = $response->getContent();

    $response->assertOk()
        ->assertSee('data-cookie-category="analytics"', false)
        ->assertSee('data-cookie-src="https://www.googletagmanager.com/gtag/js?id=G-TEST12345"', false)
        ->assertSee("window.gtag('consent', 'default'", false)
        ->assertSee("analytics_storage: 'denied'", false)
        ->assertSee("window.gtag('consent', 'update'", false)
        ->assertSee("analytics_storage: 'granted'", false)
        ->assertSee('allow_google_signals: false', false)
        ->assertSee('allow_ad_personalization_signals: false', false);

    expect($content)->not->toContain(' src="https://www.googletagmanager.com');
});

it('renders consent-gated product analytics events without customer data', function () {
    config(['analytics.google.measurement_id' => 'G-TEST12345']);
    $product = Product::factory()->published()->create([
        'name' => 'Koszulka meczowa',
        'price_grosze' => 12900,
    ]);

    $this->get(route('shop.index'))
        ->assertOk()
        ->assertSee('view_item_list', false)
        ->assertSee('data-analytics-event="select_item"', false);

    $this->get(route('shop.show', $product))
        ->assertOk()
        ->assertSee("window.etbAnalytics.track('view_item'", false)
        ->assertSee('data-analytics-event="add_to_cart"', false)
        ->assertDontSee('customer_email', false)
        ->assertDontSee('shipping_address', false);
});

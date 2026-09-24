<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use App\Services\InPostPublicPriceService;
use App\Support\ShopSettings;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

function inpostRetailFixture(string $price = '18,49'): string
{
    return '<html><h2>InPost Szybkie Nadania Kurier</h2><table><tr><td>Gabaryt B</td><td>20,49 zł</td><td>25 kg</td></tr></table>'
        .'<h2>InPost Szybkie Nadania Paczkomat</h2><table><tr><td>Gabaryt A</td><td>16,49 zł</td><td>25 kg</td></tr>'
        .'<tr><td><p>Gabaryt B</p></td><td><p>'.$price.' zł</p></td><td>25 kg</td></tr></table></html>';
}

beforeEach(function () {
    Cache::flush();
    Http::preventStrayRequests();
    $this->tariffStatus = 200;
    Http::fake([InPostPublicPriceService::URL => fn () => Http::response(inpostRetailFixture(), $this->tariffStatus)]);
    Mail::fake();
    Queue::fake();
    session()->start();
    $this->product = Product::factory()->create(['name' => 'Koszulka ETB', 'is_published' => true, 'price_grosze' => 10000]);
});

it('generates a guest message with current product and variant prices without placing an order', function () {
    $variant = $this->product->variantSizes()->create(['size_label' => 'M', 'stock_qty' => 10, 'extra_price_grosze' => 500]);
    app(CartService::class)->addItem(null, $this->product->id, $variant->id, 2);
    $this->product->update(['price_grosze' => 12000]);

    $this->get(route('cart.index'))->assertOk()->assertSee('Zamów')->assertDontSee('Przejdź do kasy');
    $response = $this->post(route('message-order.generate'), ['delivery' => 'inpost', 'price_grosze' => 1, 'shipping_grosze' => 0]);
    $response->assertOk()->assertSee('ID produktu: '.$this->product->id)->assertSee('Rozmiar: M')
        ->assertSee('Ilość: 2')->assertSee('Cena za sztukę: 125,00 zł')->assertSee('Razem z dostawą: 268,49 zł')
        ->assertSee('https://ig.me/m/eat_the_ball/')->assertSee('Kopiuj zamówienie');
    expect($response->headers->get('Cache-Control'))->toContain('no-store');
    $this->assertDatabaseCount('orders', 0);
    expect(app(CartService::class)->getItemCount(null))->toBe(2);
    expect($variant->fresh()->stock_qty)->toBe(10);
    Mail::assertNothingSent();
    Queue::assertNothingPushed();
});

it('saves the changed cart quantity when clicking order', function () {
    app(CartService::class)->addItem(null, $this->product->id, null, 1);
    $this->post(route('cart.update'), ['order' => 1, 'items' => [
        ['product_id' => $this->product->id, 'variant_size_id' => null, 'qty' => 3],
    ]])->assertRedirect(route('message-order.show'));
    $this->post(route('message-order.generate'), ['delivery' => 'pickup'])
        ->assertOk()->assertSee('Ilość: 3')->assertSee('Razem z dostawą: 300,00 zł')
        ->assertSee('Odbiór osobisty na meczu — 0,00 zł.');
});

it('keeps account carts isolated and does not clear them after generation', function () {
    $first = User::factory()->create();
    $second = User::factory()->create();
    app(CartService::class)->addItem($first, $this->product->id, null, 2);
    app(CartService::class)->addItem($second, $this->product->id, null, 7);
    $this->actingAs($first)->post(route('message-order.generate'), ['delivery' => 'pickup'])
        ->assertOk()->assertSee('Ilość: 2')->assertDontSee('Ilość: 7');
    expect(app(CartService::class)->getItemCount($first))->toBe(2);
    expect(app(CartService::class)->getItemCount($second))->toBe(7);
});

it('requires a delivery choice and redirects empty carts', function () {
    $this->get(route('message-order.show'))->assertRedirect(route('cart.index'));
    app(CartService::class)->addItem(null, $this->product->id, null, 1);
    $this->post(route('message-order.generate'), ['delivery' => 'courier'])->assertSessionHasErrors('delivery');
});

it('rejects unpublished products and foreign or missing variants without a redirect loop', function (string $invalid) {
    $variantId = null;
    if ($invalid === 'unpublished') {
        $this->product->update(['is_published' => false]);
    } elseif ($invalid === 'foreign') {
        $other = Product::factory()->create();
        $variantId = $other->variantSizes()->create(['size_label' => 'XL', 'stock_qty' => 10, 'extra_price_grosze' => 0])->id;
    } else {
        $this->product->variantSizes()->create(['size_label' => 'M', 'stock_qty' => 10, 'extra_price_grosze' => 0]);
    }
    app(CartService::class)->addItem(null, $this->product->id, $variantId, 1);
    $this->get(route('message-order.show'))->assertRedirect(route('cart.index'))->assertSessionHasErrors('cart');
    $this->post(route('message-order.generate'), ['delivery' => 'pickup'])
        ->assertRedirect(route('cart.index'))->assertSessionHasErrors('cart');
})->with(['unpublished', 'foreign', 'missing']);

it('blocks legacy payment entry points while leaving historic confirmations available to their owner', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->post(route('checkout.place'))->assertForbidden();
    $this->post(route('checkout.shipping'), [])->assertForbidden();
    $this->post(route('payment.przelewy24.webhook'), [])->assertForbidden();
    $this->get(route('checkout.shipping'))->assertRedirect(route('message-order.show'));
    $order = Order::factory()->create(['user_id' => $user->id]);
    $this->get(route('checkout.confirmation', $order))->assertOk();
    $this->actingAs(User::factory()->create())->get(route('checkout.confirmation', $order))->assertForbidden();
    Http::assertNothingSent();
});

it('allows only administrators to change settings or the global mode', function (string $role) {
    $this->actingAs(User::factory()->create(['role' => $role]));
    $this->get(route('admin.shop-settings.edit'))->assertForbidden();
    $this->put(route('admin.shop-settings.update'), [])->assertForbidden();
    $this->patch(route('admin.shop-settings.mode'), ['mode' => 'legacy', 'confirmation' => 'ZMIENIAM TRYB SKLEPU', 'acknowledged' => 1])->assertForbidden();
    expect(ShopSettings::legacy())->toBeFalse();
})->with(['fan', 'employee']);

it('requires the explicit phrase and acknowledgement before switching the whole shop', function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']));
    $this->get(route('admin.shop-settings.edit'))->assertOk()->assertSee('ZMIENIAM TRYB SKLEPU');
    $this->patch(route('admin.shop-settings.mode'), ['mode' => 'legacy'])->assertSessionHasErrors(['confirmation', 'acknowledged']);
    expect(ShopSettings::legacy())->toBeFalse();
    $this->patch(route('admin.shop-settings.mode'), ['mode' => 'legacy', 'confirmation' => 'ZMIENIAM TRYB SKLEPU', 'acknowledged' => 1])->assertRedirect();
    expect(ShopSettings::legacy())->toBeTrue();
    $this->get(route('message-order.show'))->assertRedirect(route('checkout.shipping'));
    $this->patch(route('admin.shop-settings.mode'), ['mode' => 'message', 'confirmation' => 'ZMIENIAM TRYB SKLEPU', 'acknowledged' => 1])->assertRedirect();
    expect(ShopSettings::legacy())->toBeFalse();
});

it('uses admin contact and payment instructions as escaped text', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin)->put(route('admin.shop-settings.update'), [
        'email' => 'zamowienia@example.com', 'payment' => 'Potwierdź przelew. <script>alert(1)</script>',
        'inpost' => 'Podaj kod Paczkomatu.', 'pickup' => 'Odbiór przy wejściu.',
    ])->assertRedirect()->assertSessionHasNoErrors();
    app(CartService::class)->addItem($admin, $this->product->id, null, 1);
    $this->post(route('message-order.generate'), ['delivery' => 'pickup'])
        ->assertOk()->assertSee('zamowienia@example.com')->assertSee('Odbiór przy wejściu.')
        ->assertSee('Potwierdź przelew. <script>alert(1)</script>')->assertDontSee('<script>alert(1)</script>', false);
});

it('reads only the retail locker B price and refreshes it after six hours', function () {
    $service = app(InPostPublicPriceService::class);
    expect($service->quote()['price_grosze'])->toBe(1849);
    expect($service->quote()['price_grosze'])->toBe(1849);
    Http::assertSentCount(1);
    $this->travel(6)->hours();
    $service->quote();
    Http::assertSentCount(2);
});

it('keeps pickup available and refuses an unverified inpost price on outage', function () {
    $this->tariffStatus = 503;
    app(CartService::class)->addItem(null, $this->product->id, null, 1);
    $this->post(route('message-order.generate'), ['delivery' => 'inpost'])->assertSessionHasErrors('delivery');
    $this->post(route('message-order.generate'), ['delivery' => 'pickup'])->assertOk()->assertSee('Razem z dostawą: 100,00 zł');
    Http::assertSentCount(1);
});

it('rejects ambiguous or changed tariff markup', function () {
    $service = app(InPostPublicPriceService::class);
    expect(fn () => $service->parse(inpostRetailFixture().inpostRetailFixture()))->toThrow(RuntimeException::class);
    expect(fn () => $service->parse('<h2>Cennik biznesowy</h2><table><tr><td>Gabaryt B</td><td>1,00 zł</td><td>25 kg</td></tr></table>'))->toThrow(RuntimeException::class);
});

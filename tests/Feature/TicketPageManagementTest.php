<?php

use App\Models\AppSetting;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    config(['filesystems.media_disk' => 'media']);
    Storage::fake('media');
});

function fakeTicketGraphic(string $name): UploadedFile
{
    return UploadedFile::fake()->createWithContent($name, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII='));
}

it('lets panel users manage ticket page content with an image and custom button label', function (string $role): void {
    $user = User::factory()->create(['role' => $role]);

    $this->actingAs($user)
        ->get(route('profile.edit', ['section' => 'tickets']))
        ->assertOk()
        ->assertSee('Grafika strony biletów')
        ->assertSee('Podpis przycisku');

    $this->put(route('admin.tickets-page.update'), [
        'body' => "Sprzedaż rusza w piątek.\nLiczba miejsc jest ograniczona.",
        'button_url' => 'https://bilety.example.com/mecz',
        'button_label' => 'Zarezerwuj miejsce',
        'image' => fakeTicketGraphic('bilety.png'),
    ])->assertRedirect(route('profile.edit', ['section' => 'tickets']));

    expect(AppSetting::getValue('tickets_page_body'))->toBe("Sprzedaż rusza w piątek.\nLiczba miejsc jest ograniczona.");
    expect(AppSetting::getValue('tickets_page_button_url'))->toBe('https://bilety.example.com/mecz');
    expect(AppSetting::getValue('tickets_page_button_label'))->toBe('Zarezerwuj miejsce');
    expect(AppSetting::getValue('tickets_page_image'))->toStartWith('tickets/');
    Storage::disk('media')->assertExists(AppSetting::getValue('tickets_page_image'));

    $this->get(route('tickets'))
        ->assertOk()
        ->assertSee('Sprzedaż rusza w piątek.')
        ->assertSee('Zarezerwuj miejsce')
        ->assertSee('https://bilety.example.com/mecz', false)
        ->assertSee('tickets/', false);
})->with([User::ROLE_ADMIN, User::ROLE_EMPLOYEE]);

it('can replace and remove the ticket page image without losing saved text', function (): void {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

    $this->actingAs($admin)->put(route('admin.tickets-page.update'), [
        'body' => 'Stała treść biletów.',
        'image' => fakeTicketGraphic('pierwsza.png'),
    ])->assertSessionHasNoErrors();

    $firstPath = AppSetting::getValue('tickets_page_image');
    Storage::disk('media')->assertExists($firstPath);

    $this->put(route('admin.tickets-page.update'), [
        'body' => 'Stała treść biletów.',
        'image' => fakeTicketGraphic('druga.png'),
    ])->assertSessionHasNoErrors();

    $secondPath = AppSetting::getValue('tickets_page_image');
    expect($secondPath)->not->toBe($firstPath);
    Storage::disk('media')->assertMissing($firstPath);
    Storage::disk('media')->assertExists($secondPath);

    $this->put(route('admin.tickets-page.update'), [
        'body' => 'Stała treść biletów.',
        'remove_image' => '1',
    ])->assertSessionHasNoErrors();

    expect(AppSetting::getValue('tickets_page_body'))->toBe('Stała treść biletów.');
    expect(AppSetting::getValue('tickets_page_image'))->toBeNull();
    Storage::disk('media')->assertMissing($secondPath);
});

it('blocks guests and fans from editing the ticket page', function (): void {
    $this->put(route('admin.tickets-page.update'), ['body' => 'Zmiana'])->assertRedirect(route('login'));

    $this->actingAs(User::factory()->create(['role' => User::ROLE_FAN]))
        ->put(route('admin.tickets-page.update'), ['body' => 'Zmiana'])
        ->assertForbidden();

    expect(AppSetting::getValue('tickets_page_body'))->toBeNull();
});

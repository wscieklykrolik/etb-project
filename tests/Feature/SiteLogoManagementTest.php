<?php

use App\Models\AppSetting;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    config(['filesystems.media_disk' => 'media']);
    Storage::fake('media');
});

function fakeLogo(string $name): UploadedFile
{
    return UploadedFile::fake()->createWithContent($name, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII='));
}

it('lets an admin upload separate logos for every marked site area', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

    $logoKeys = [
        'club' => 'club_logo',
        'title-sponsor' => 'title_sponsor_logo',
        'academy' => 'academy_logo',
        'shop' => 'shop_logo',
        'tickets' => 'tickets_logo',
        'admin' => 'admin_logo',
        'auth' => 'auth_logo',
        'browser' => 'browser_logo',
    ];

    foreach ($logoKeys as $logo => $settingKey) {
        $payload = ['logo' => fakeLogo($logo.'.png')];

        if ($logo === 'title-sponsor') {
            $payload['url'] = 'https://sponsor.example.com';
        }

        $this->actingAs($admin)->patch(route('admin.site-logos.update', $logo), $payload)->assertRedirect();

        $logoPath = AppSetting::getValue($settingKey);

        expect($logoPath)->toStartWith('logos/');
        Storage::disk('media')->assertExists($logoPath);
    }

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('<title>Strona główna | ETB Łódź</title>', false)
        ->assertSee('Eat The Ball - oficjalna strona')
        ->assertSee('ETB Łódź')
        ->assertSee('Logo sponsora tytularnego')
        ->assertSee('logos/', false);

    $this->get(route('news.index'))
        ->assertOk()
        ->assertSee('<title>Aktualności | ETB Łódź</title>', false)
        ->assertSee('rel="icon"', false)
        ->assertSee(AppSetting::getValue('browser_logo'), false);

    $this->get(route('academy'))
        ->assertOk()
        ->assertSee('Logo akademii')
        ->assertSee('logos/', false);

    $this->get(route('shop.index'))
        ->assertOk()
        ->assertSee('Logo sklepu')
        ->assertSee('logos/', false);

    $this->get(route('tickets'))
        ->assertOk()
        ->assertSee('Logo biletów')
        ->assertSee('logos/', false);

    $this->get(route('profile.edit', ['section' => 'dashboard']))
        ->assertOk()
        ->assertSee('Logo panelu admina')
        ->assertSee('Logo karty przeglądarki')
        ->assertSee('logos/', false);

    auth()->logout();

    $this->get(route('login'))
        ->assertOk()
        ->assertSee('Logowanie ETB Łódź')
        ->assertSee('Logo panelu logowania')
        ->assertSee('logos/', false);
});

it('lets an admin remove a selected site logo without deleting the others', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    Storage::disk('media')->put('logos/klub.png', 'logo');
    Storage::disk('media')->put('logos/sponsor.png', 'logo');
    AppSetting::setValue('club_logo', 'logos/klub.png');
    AppSetting::setValue('title_sponsor_logo', 'logos/sponsor.png');

    $this->actingAs($admin)->delete(route('admin.site-logos.destroy', 'title-sponsor'))->assertRedirect();

    expect(AppSetting::getValue('title_sponsor_logo'))->toBeNull();
    expect(AppSetting::getValue('club_logo'))->toBe('logos/klub.png');
    Storage::disk('media')->assertMissing('logos/sponsor.png');
    Storage::disk('media')->assertExists('logos/klub.png');
});

it('uses the previous site logo as the club logo fallback and migrates it on upload', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    Storage::disk('media')->put('logos/stare-logo.png', 'logo');
    AppSetting::setValue('site_logo', 'logos/stare-logo.png');

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('logos/stare-logo.png', false);

    $this->actingAs($admin)->patch(route('admin.site-logos.update', 'club'), [
        'logo' => fakeLogo('nowe-logo.png'),
    ])->assertRedirect();

    expect(AppSetting::getValue('site_logo'))->toBeNull();
    expect(AppSetting::getValue('club_logo'))->toStartWith('logos/');
    Storage::disk('media')->assertMissing('logos/stare-logo.png');
});

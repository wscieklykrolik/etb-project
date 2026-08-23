<?php

use App\Models\AppSetting;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    config(['filesystems.media_disk' => 'media']);
    Storage::fake('media');
});

it('lets an admin upload a site logo and renders it in the public header', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

    $this->actingAs($admin)->patch(route('admin.site-logo.update'), [
        'site_logo' => UploadedFile::fake()->createWithContent('logo-etb.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII=')),
    ])->assertRedirect();

    $logoPath = AppSetting::getValue('site_logo');

    expect($logoPath)->toStartWith('logos/');
    Storage::disk('media')->assertExists($logoPath);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Logo ETB')
        ->assertSee('logos/', false);
});

it('lets an admin remove the site logo', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    Storage::disk('media')->put('logos/stare-logo.png', 'logo');
    AppSetting::setValue('site_logo', 'logos/stare-logo.png');

    $this->actingAs($admin)->delete(route('admin.site-logo.destroy'))->assertRedirect();

    expect(AppSetting::getValue('site_logo'))->toBeNull();
    Storage::disk('media')->assertMissing('logos/stare-logo.png');
});

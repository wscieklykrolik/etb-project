<?php

use App\Models\Sponsor;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    config(['filesystems.media_disk' => 'media']);
    Storage::fake('media');
});

it('stores uploaded media on the configured media disk and keeps a relative path in the database', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $logo = UploadedFile::fake()->create('oryginalne-logo.png', 12, 'image/png');

    $this->actingAs($admin)->post(route('sponsors.store'), [
        'name' => 'Partner Media Disk',
        'type' => Sponsor::TYPE_TECHNOLOGY,
        'url' => 'https://partner.example.com',
        'logo' => $logo,
        'sort_order' => 5,
        'is_active' => '1',
    ])->assertRedirect(route('profile.edit'));

    $sponsor = Sponsor::query()->firstOrFail();

    expect($sponsor->logo_path)
        ->toStartWith('sponsors/')
        ->not->toContain('oryginalne-logo')
        ->not->toStartWith('http')
        ->not->toStartWith('/storage/');

    Storage::disk('media')->assertExists($sponsor->logo_path);
    $this->assertDatabaseHas('sponsors', [
        'id' => $sponsor->id,
        'logo_path' => $sponsor->logo_path,
    ]);
});

it('replaces uploaded media on update and deletes the old file from the configured media disk', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $oldPath = 'sponsors/stare-logo.png';
    Storage::disk('media')->put($oldPath, 'old-logo');

    $sponsor = Sponsor::query()->create([
        'name' => 'Partner do podmiany',
        'type' => Sponsor::TYPE_SPONSOR,
        'url' => 'https://old.example.com',
        'logo_path' => $oldPath,
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $this->actingAs($admin)->patch(route('sponsors.update', $sponsor), [
        'name' => 'Partner po podmianie',
        'type' => Sponsor::TYPE_PARTNER,
        'url' => 'https://new.example.com',
        'logo' => UploadedFile::fake()->create('nowe-logo.png', 12, 'image/png'),
        'sort_order' => 2,
        'is_active' => '1',
    ])->assertRedirect(route('profile.edit'));

    $sponsor->refresh();

    expect($sponsor->logo_path)->not->toBe($oldPath);
    Storage::disk('media')->assertExists($sponsor->logo_path);
    Storage::disk('media')->assertMissing($oldPath);
});

it('deletes uploaded media from the configured media disk when the record is deleted', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $path = 'sponsors/do-usuniecia.png';
    Storage::disk('media')->put($path, 'logo');

    $sponsor = Sponsor::query()->create([
        'name' => 'Partner do usunięcia',
        'type' => Sponsor::TYPE_SPONSOR,
        'url' => 'https://delete.example.com',
        'logo_path' => $path,
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $this->actingAs($admin)->delete(route('sponsors.destroy', $sponsor))->assertRedirect();

    $this->assertDatabaseMissing('sponsors', ['id' => $sponsor->id]);
    Storage::disk('media')->assertMissing($path);
});

it('generates public media urls from the configured disk instead of assuming the storage path', function () {
    config([
        'filesystems.media_disk' => 'media_url_test',
        'filesystems.disks.media_url_test' => [
            'driver' => 'local',
            'root' => storage_path('framework/testing/disks/media-url-test'),
            'url' => 'https://media.example.test',
            'visibility' => 'public',
            'throw' => false,
        ],
    ]);

    Sponsor::query()->create([
        'name' => 'Partner z CDN',
        'type' => Sponsor::TYPE_TECHNOLOGY,
        'url' => 'https://cdn.example.com',
        'logo_path' => 'sponsors/cdn-logo.png',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $this->get(route('club.sponsors'))
        ->assertOk()
        ->assertSee('https://media.example.test/sponsors/cdn-logo.png')
        ->assertDontSee('/storage/sponsors/cdn-logo.png');
});

<?php

use App\Models\Sponsor;
use App\Models\SponsorCategory;
use App\Models\User;
use App\Support\MediaStorage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('shows active sponsors in the public footer grouped by type', function () {
    Sponsor::query()->create([
        'name' => 'Strategic Logo',
        'type' => Sponsor::TYPE_STRATEGIC,
        'url' => 'https://strategic.example.com',
        'logo_path' => 'sponsors/strategic.png',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    Sponsor::query()->create([
        'name' => 'Hidden Sponsor',
        'type' => Sponsor::TYPE_SPONSOR,
        'url' => 'https://hidden.example.com',
        'logo_path' => 'sponsors/hidden.png',
        'sort_order' => 2,
        'is_active' => false,
    ]);

    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertSee('Partner strategiczny');
    $response->assertSee('Strategic Logo');
    $response->assertSee('https://strategic.example.com');
    $response->assertDontSee('Partner technologiczny');
    $response->assertDontSee('Hidden Sponsor');
});

it('lets an admin create and delete sponsors with logo and link', function () {
    Storage::fake('public');

    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $category = SponsorCategory::query()->where('legacy_type', Sponsor::TYPE_TECHNOLOGY)->firstOrFail();
    $logo = UploadedFile::fake()->create('logo.png', 12, 'image/png');

    $createResponse = $this->actingAs($admin)->post(route('sponsors.store'), [
        'name' => 'Partner Testowy',
        'sponsor_category_id' => $category->id,
        'url' => 'https://partner.example.com',
        'logo' => $logo,
        'sort_order' => 7,
        'is_active' => '1',
    ]);

    $sponsor = Sponsor::query()->firstOrFail();

    $createResponse->assertRedirect(route('profile.edit'));
    expect($sponsor->name)->toBe('Partner Testowy');
    expect($sponsor->sponsor_category_id)->toBe($category->id);
    expect($sponsor->type)->toBe(Sponsor::TYPE_TECHNOLOGY);
    expect($sponsor->url)->toBe('https://partner.example.com');
    Storage::disk('public')->assertExists($sponsor->logo_path);

    $deleteResponse = $this->actingAs($admin)->delete(route('sponsors.destroy', $sponsor));

    $deleteResponse->assertRedirect();
    $this->assertDatabaseMissing('sponsors', ['id' => $sponsor->id]);
    Storage::disk('public')->assertMissing($sponsor->logo_path);
});

it('lets an admin assign separate images to the footer and homepage using one link', function () {
    Storage::fake('public');

    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $category = SponsorCategory::query()->where('legacy_type', Sponsor::TYPE_SPONSOR)->firstOrFail();

    $response = $this->actingAs($admin)->post(route('sponsors.store'), [
        'name' => 'Sponsor z dwoma zdjęciami',
        'sponsor_category_id' => $category->id,
        'url' => 'https://wspolny-link.example.com',
        'use_same_logo' => '0',
        'logo' => UploadedFile::fake()->create('stopka.png', 12, 'image/png'),
        'homepage_logo' => UploadedFile::fake()->create('strona-glowna.png', 12, 'image/png'),
        'sort_order' => 3,
        'is_active' => '1',
    ]);

    $response->assertRedirect(route('profile.edit'));

    $sponsor = Sponsor::query()->firstOrFail();

    expect($sponsor->homepage_logo_path)->not->toBeNull()
        ->and($sponsor->homepage_logo_path)->not->toBe($sponsor->logo_path)
        ->and($sponsor->homepageLogoPath())->toBe($sponsor->homepage_logo_path);

    Storage::disk('public')->assertExists($sponsor->logo_path);
    Storage::disk('public')->assertExists($sponsor->homepage_logo_path);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('https://wspolny-link.example.com')
        ->assertSee(MediaStorage::url($sponsor->logo_path))
        ->assertSee(MediaStorage::url($sponsor->homepage_logo_path));
});

it('requires a homepage image when separate images are selected', function () {
    Storage::fake('public');

    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $category = SponsorCategory::query()->where('legacy_type', Sponsor::TYPE_SPONSOR)->firstOrFail();

    $this->actingAs($admin)->post(route('sponsors.store'), [
        'name' => 'Sponsor bez drugiego zdjęcia',
        'sponsor_category_id' => $category->id,
        'url' => 'https://partner.example.com',
        'use_same_logo' => '0',
        'logo' => UploadedFile::fake()->create('stopka.png', 12, 'image/png'),
        'sort_order' => 3,
        'is_active' => '1',
    ])->assertSessionHasErrors('homepage_logo');
});

it('can return to one shared image and removes the unused homepage file', function () {
    Storage::fake('public');

    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $category = SponsorCategory::query()->where('legacy_type', Sponsor::TYPE_SPONSOR)->firstOrFail();
    Storage::disk('public')->put('sponsors/wspolne.png', 'shared');
    Storage::disk('public')->put('sponsors/glowna.png', 'homepage');

    $sponsor = Sponsor::query()->create([
        'name' => 'Sponsor wracający do wspólnego zdjęcia',
        'sponsor_category_id' => $category->id,
        'url' => 'https://partner.example.com',
        'logo_path' => 'sponsors/wspolne.png',
        'homepage_logo_path' => 'sponsors/glowna.png',
        'sort_order' => 3,
        'is_active' => true,
    ]);

    $this->actingAs($admin)->put(route('sponsors.update', $sponsor), [
        'name' => $sponsor->name,
        'sponsor_category_id' => $category->id,
        'url' => $sponsor->url,
        'use_same_logo' => '1',
        'sort_order' => 3,
        'is_active' => '1',
    ])->assertRedirect(route('profile.edit'));

    expect($sponsor->fresh()->homepage_logo_path)->toBeNull();
    Storage::disk('public')->assertMissing('sponsors/glowna.png');
    Storage::disk('public')->assertExists('sponsors/wspolne.png');
});

it('lets an admin manage sponsor categories', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

    $createResponse = $this->actingAs($admin)->post(route('sponsor-categories.store'), [
        'name' => 'Partnerzy lokalni',
        'sort_order' => 55,
        'is_active' => '1',
    ]);

    $category = SponsorCategory::query()->where('name', 'Partnerzy lokalni')->firstOrFail();

    $createResponse->assertRedirect(route('profile.edit', ['section' => 'sponsors']));
    expect($category->sort_order)->toBe(55);
    expect($category->is_active)->toBeTrue();

    $this->actingAs($admin)->put(route('sponsor-categories.update', $category), [
        'name' => 'Partnerzy miejscy',
        'sort_order' => 60,
    ])->assertRedirect(route('profile.edit', ['section' => 'sponsors']));

    $category->refresh();
    expect($category->name)->toBe('Partnerzy miejscy');
    expect($category->is_active)->toBeFalse();

    $this->actingAs($admin)->delete(route('sponsor-categories.destroy', $category))->assertRedirect(route('profile.edit', ['section' => 'sponsors']));
    $this->assertDatabaseMissing('sponsor_categories', ['id' => $category->id]);
});

it('shows active sponsors on the club sponsors page with large white logo tiles', function () {
    Sponsor::query()->create([
        'name' => 'White Tile Partner',
        'type' => Sponsor::TYPE_TECHNOLOGY,
        'url' => 'https://technology.example.com',
        'logo_path' => 'sponsors/technology.png',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    Sponsor::query()->create([
        'name' => 'Inactive Tile Partner',
        'type' => Sponsor::TYPE_PARTNER,
        'url' => 'https://inactive.example.com',
        'logo_path' => 'sponsors/inactive.png',
        'sort_order' => 1,
        'is_active' => false,
    ]);

    $response = $this->get(route('club.sponsors'));

    $response->assertOk();
    $response->assertSee('Partner technologiczny');
    $response->assertSee('White Tile Partner');
    $response->assertSee('bg-white');
    $response->assertSee('max-h-28');
    $response->assertDontSee('Inactive Tile Partner');
});

<?php

use App\Models\ImportantPage;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('links the footer to all important pages and the new office email', function () {
    foreach (ImportantPage::PAGES as $slug => $title) {
        $response = $this->get(route('important-pages.show', $slug));
        $response->assertOk()->assertSee($title)
            ->assertSee('mailto:etb.3x3@gmail.com', false)
            ->assertDontSee('Biuro prasowe');
        foreach (array_keys(ImportantPage::PAGES) as $linkedSlug) {
            $response->assertSee(route('important-pages.show', $linkedSlug), false);
        }
    }
    $this->get('/informacje/nieznana')->assertNotFound();
});

it('lets panel users save text and shows it escaped on the public page', function (string $role) {
    $user = User::factory()->create(['role' => $role]);
    $this->actingAs($user)->get(route('profile.edit', ['section' => 'important-links']))
        ->assertOk()->assertSee('name="body"', false);
    $body = "Pierwszy akapit.\nDrugi akapit. <script>alert(1)</script>";
    $this->put(route('admin.important-pages.update', 'regulamin-sklepu'), ['body' => $body])
        ->assertRedirect(route('profile.edit', ['section' => 'important-links']));
    expect(ImportantPage::first()->body)->toBe($body);
    $this->get(route('important-pages.show', 'regulamin-sklepu'))
        ->assertOk()->assertSee($body)->assertDontSee('<script>alert(1)</script>', false);
})->with([User::ROLE_ADMIN, User::ROLE_EMPLOYEE]);

it('supports image only, replacement, preservation and removal', function () {
    Storage::fake('public');
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $upload = fn () => UploadedFile::fake()->createWithContent('dokument.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII='));
    $url = route('admin.important-pages.update', 'polityka-prywatnosci');
    $this->actingAs($admin)->put($url, ['image' => $upload()])->assertSessionHasNoErrors();
    $page = ImportantPage::firstOrFail();
    $firstPath = $page->image_path;
    expect($page->body)->toBeNull();
    Storage::disk('public')->assertExists($firstPath);
    $this->get(route('important-pages.show', $page->slug))->assertSee('storage/'.$firstPath);

    $this->put($url, ['body' => 'Treść i zdjęcie.'])->assertSessionHasNoErrors();
    expect($page->fresh()->image_path)->toBe($firstPath);
    $this->put($url, ['body' => 'Nowa treść.', 'image' => $upload()])->assertSessionHasNoErrors();
    $secondPath = $page->fresh()->image_path;
    expect($secondPath)->not->toBe($firstPath);
    Storage::disk('public')->assertMissing($firstPath);
    Storage::disk('public')->assertExists($secondPath);

    $this->put($url, ['body' => 'Tylko tekst.', 'remove_image' => '1'])->assertSessionHasNoErrors();
    expect($page->fresh()->image_path)->toBeNull();
    Storage::disk('public')->assertMissing($secondPath);
});

it('rejects non-images without changing saved content', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    ImportantPage::create(['slug' => 'regulamin-zwrotow', 'body' => 'Poprzednia treść.']);
    $this->actingAs($admin)->from(route('profile.edit', ['section' => 'important-links']))
        ->put(route('admin.important-pages.update', 'regulamin-zwrotow'), [
            'body' => 'Nowa treść.',
            'image' => UploadedFile::fake()->create('plik.pdf', 10, 'application/pdf'),
        ])->assertSessionHasErrors(['image'], null, 'regulamin-zwrotow');
    expect(ImportantPage::first()->body)->toBe('Poprzednia treść.');
});

it('blocks guests and fans from editing important pages', function () {
    $url = route('admin.important-pages.update', 'regulamin-sklepu');
    $this->put($url, ['body' => 'Zmiana'])->assertRedirect(route('login'));
    $this->actingAs(User::factory()->create(['role' => User::ROLE_FAN]))
        ->put($url, ['body' => 'Zmiana'])->assertForbidden();
    expect(ImportantPage::count())->toBe(0);
});

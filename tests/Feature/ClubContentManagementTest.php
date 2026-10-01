<?php

use App\Models\ClubSection;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('lets an admin edit a club section with text and photos', function () {
    Storage::fake('public');

    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $fakePng = function (string $name): UploadedFile {
        $path = tempnam(sys_get_temp_dir(), 'club-section-');

        file_put_contents(
            $path,
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII=')
        );

        return new UploadedFile($path, $name, 'image/png', null, true);
    };

    $response = $this->actingAs($admin)->put(route('admin.club-sections.update', 'history'), [
        'body' => 'Historia ETB od pierwszego treningu do dzisiejszych rozgrywek.',
        'photos' => [
            $fakePng('historia-1.png'),
            $fakePng('historia-2.png'),
        ],
    ]);

    $response->assertRedirect(route('profile.edit'));

    $section = ClubSection::query()->where('slug', 'history')->firstOrFail();

    expect($section->body)->toContain('Historia ETB')
        ->and($section->images)->toHaveCount(2);

    Storage::disk('public')->assertExists($section->images->first()->image_path);

    $publicResponse = $this->get(route('club.history'));

    $publicResponse->assertOk();
    $publicResponse->assertSee('Historia ETB od pierwszego treningu');
    $publicResponse->assertSee('storage/'.$section->images->first()->image_path);
});

it('hides empty image space and displays image captions on public club pages', function () {
    Storage::fake('public');

    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $fakePng = function (string $name): UploadedFile {
        $path = tempnam(sys_get_temp_dir(), 'club-section-');

        file_put_contents(
            $path,
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII=')
        );

        return new UploadedFile($path, $name, 'image/png', null, true);
    };

    $this->actingAs($admin)->put(route('admin.club-sections.update', 'business'), [
        'body' => 'Oferta biznesowa bez zdjęć.',
    ])->assertRedirect(route('profile.edit'));

    $this->get(route('club.business'))
        ->assertOk()
        ->assertSee('Oferta biznesowa bez zdjęć.')
        ->assertDontSee('Zdjęcia');

    $this->actingAs($admin)->put(route('admin.club-sections.update', 'venue'), [
        'body' => 'Opis obiektu.',
        'photos' => [$fakePng('obiekt.png')],
    ])->assertRedirect(route('profile.edit'));

    $section = ClubSection::query()->where('slug', 'venue')->firstOrFail();
    $image = $section->images()->firstOrFail();

    $this->actingAs($admin)->patch(route('admin.club-sections.images.update', [$section, $image]), [
        'caption' => 'Fot. Jan Kowalski / ETB',
    ])->assertRedirect(route('profile.edit'));

    $this->get(route('club.venue'))
        ->assertOk()
        ->assertSee('Fot. Jan Kowalski / ETB');
});

it('does not expose the removed investors club section', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

    ClubSection::query()->create([
        'slug' => 'investors',
        'title' => 'Inwestorzy',
        'sort_order' => 4,
    ]);

    $this->get('/club/investors')->assertNotFound();

    $this->actingAs($admin)
        ->get(route('profile.edit'))
        ->assertOk()
        ->assertDontSee('Inwestorzy');
});

it('uses the editable club contact content on both contact routes', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

    $this->actingAs($admin)->put(route('admin.club-sections.update', 'contact'), [
        'body' => 'Kontakt z biurem ETB: kontakt@etb.test',
    ])->assertRedirect(route('profile.edit'));

    $this->get(route('club.contact'))
        ->assertOk()
        ->assertSee('Kontakt z biurem ETB: kontakt@etb.test');

    $this->get(route('contact'))
        ->assertOk()
        ->assertSee('Kontakt z biurem ETB: kontakt@etb.test')
        ->assertDontSee('Sekcja gotowa do dodawania treści');
});

it('lets an admin change office and marketing email addresses from the contact tab', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

    $this->actingAs($admin)
        ->get(route('profile.edit', ['section' => 'contact']))
        ->assertOk()
        ->assertSee('Adres e-mail biura')
        ->assertSee('Adres e-mail marketingu i mediów');

    $this->actingAs($admin)->put(route('admin.contact-emails.update'), [
        'office_email' => 'biuro@etb.test',
        'marketing_email' => 'marketing@etb.test',
    ])->assertRedirect(route('profile.edit', ['section' => 'contact']));

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('mailto:biuro@etb.test', false)
        ->assertSee('mailto:marketing@etb.test', false);

    $this->get(route('contact'))
        ->assertOk()
        ->assertSee('mailto:marketing@etb.test', false);

    $this->get(route('cookies.policy'))
        ->assertOk()
        ->assertSee('mailto:biuro@etb.test', false);
});

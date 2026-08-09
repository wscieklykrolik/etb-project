<?php

use App\Models\News;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('lets admins create a gallery news item without article content', function () {
    Storage::fake('public');

    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

    $response = $this->actingAs($admin)->post(route('news.store'), [
        'type' => News::TYPE_GALLERY,
        'title' => 'Galeria z meczu',
        'excerpt' => 'Najlepsze zdjęcia z ostatniego spotkania.',
        'photo_author' => 'Jan Kowalski',
        'publish_at' => now()->addHour()->format('Y-m-d H:i:s'),
        'is_visible' => '1',
        'gallery' => [
            uploadedTestImage('one.png'),
            uploadedTestImage('two.png'),
            uploadedTestImage('three.png'),
        ],
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('profile.edit', ['section' => 'news']));

    $news = News::query()->with('images')->firstOrFail();

    expect($news->type)->toBe(News::TYPE_GALLERY)
        ->and($news->content)->toBe('Najlepsze zdjęcia z ostatniego spotkania.')
        ->and($news->photo_author)->toBe('Jan Kowalski')
        ->and($news->images)->toHaveCount(3);
});

it('shows public article and photo author signatures on a news detail page', function () {
    $author = User::factory()->create(['role' => User::ROLE_ADMIN]);

    $news = News::query()->create([
        'type' => News::TYPE_ARTICLE,
        'title' => 'Zapowiedź meczu',
        'content' => 'Pełna treść zapowiedzi meczu dla kibiców.',
        'excerpt' => 'Krótka zapowiedź meczu.',
        'article_author' => 'Anna Nowak',
        'photo_author' => 'Piotr Wiśniewski',
        'author_id' => $author->id,
        'publish_at' => now()->subMinute(),
        'is_visible' => true,
    ]);

    $response = $this->get(route('news.show', $news));

    $response->assertOk();
    $response->assertSee('Autor artykułu: Anna Nowak');
    $response->assertSee('Autor zdjęć: Piotr Wiśniewski');
});

it('embeds youtube videos on the public news page', function () {
    $author = User::factory()->create(['role' => User::ROLE_ADMIN]);

    $news = News::query()->create([
        'type' => News::TYPE_VIDEO,
        'title' => 'Wywiad po meczu',
        'content' => 'Krótki wywiad po meczu.',
        'excerpt' => 'Krótki wywiad po meczu.',
        'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        'author_id' => $author->id,
        'publish_at' => now()->subMinute(),
        'is_visible' => true,
    ]);

    $response = $this->get(route('news.show', $news));

    $response->assertOk();
    $response->assertSee('https://www.youtube.com/embed/dQw4w9WgXcQ', false);
    $response->assertSee('Wywiad po meczu');
});

it('keeps scheduled galleries hidden until their publication time', function () {
    $author = User::factory()->create(['role' => User::ROLE_ADMIN]);

    $news = News::query()->create([
        'type' => News::TYPE_GALLERY,
        'title' => 'Zaplanowana galeria',
        'content' => 'Opis galerii.',
        'excerpt' => 'Opis galerii.',
        'author_id' => $author->id,
        'publish_at' => now()->addHour(),
        'is_visible' => true,
    ]);

    $this->get(route('news.show', $news))->assertNotFound();

    $news->update(['publish_at' => now()->subMinute()]);

    $this->get(route('news.show', $news))->assertOk();
});

function uploadedTestImage(string $name): UploadedFile
{
    $path = tempnam(sys_get_temp_dir(), 'etb-news-image-');
    file_put_contents($path, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAFgwJ/lwW5WQAAAABJRU5ErkJggg=='));

    return new UploadedFile($path, $name, 'image/png', null, true);
}

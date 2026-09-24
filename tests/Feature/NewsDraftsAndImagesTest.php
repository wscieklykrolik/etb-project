<?php

use App\Models\News;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    $this->admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $this->actingAs($this->admin);
});

function newsDraftImage(string $name): UploadedFile
{
    return UploadedFile::fake()->createWithContent($name, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII='));
}

function editableNews(User $author, array $attributes = []): News
{
    return News::query()->create(array_merge([
        'title' => 'Istniejący wpis',
        'type' => News::TYPE_ARTICLE,
        'content' => 'Kompletna treść istniejącej aktualności.',
        'author_id' => $author->id,
        'is_visible' => true,
    ], $attributes));
}

it('saves an incomplete entry as a private draft', function () {
    $this->postJson(route('news.store'), [
        'type' => News::TYPE_GALLERY,
        'save_as_draft' => true,
        'title' => 'Rozpoczęta galeria',
        'gallery' => [newsDraftImage('one.png')],
        'is_visible' => true,
    ])->assertOk();

    $news = News::query()->with('images')->firstOrFail();

    expect($news->is_draft)->toBeTrue()
        ->and($news->is_visible)->toBeFalse()
        ->and($news->isPubliclyVisible())->toBeFalse()
        ->and($news->images)->toHaveCount(1);
    $this->get(route('news.show', $news))->assertNotFound();
});

it('removes only selected gallery images and keeps the remaining ones', function () {
    $news = editableNews($this->admin, ['type' => News::TYPE_GALLERY, 'excerpt' => 'Opis galerii.']);
    $removed = $news->images()->create(['path' => 'news/remove.png', 'sort_order' => 0]);
    $kept = $news->images()->create(['path' => 'news/keep.png', 'sort_order' => 1]);
    Storage::disk('public')->put($removed->path, 'remove');
    Storage::disk('public')->put($kept->path, 'keep');

    $this->put(route('news.update', $news), [
        'type' => News::TYPE_GALLERY,
        'title' => $news->title,
        'excerpt' => $news->excerpt,
        'is_visible' => true,
        'remove_images' => [$removed->id],
    ])->assertSessionHasNoErrors();

    expect($news->fresh()->images)->toHaveCount(1)
        ->and($news->fresh()->images->sole()->id)->toBe($kept->id);
    Storage::disk('public')->assertMissing($removed->path);
    Storage::disk('public')->assertExists($kept->path);
});

it('does not carry values from an edited entry into the new entry form', function () {
    $news = editableNews($this->admin, ['title' => 'Wpis do edycji']);

    $response = $this->get(route('profile.edit', ['section' => 'news']))->assertOk();
    $html = $response->getContent();

    preg_match('~action="[^"]*/news"[^>]*>(.*?)</form>~s', $html, $matches);

    expect($matches[1])->toContain('name="title" required value=""')
        ->not->toContain('value="'.$news->title.'"');
    $response->assertSeeInOrder(['Opublikowane', 'Zaplanowane', 'Wersje robocze'], false);
});

<?php

use App\Models\News;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows balanced cookie choices and permanent access to settings', function () {
    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertSee('Dbamy o Twoją prywatność')
        ->assertSee('Odrzuć opcjonalne')
        ->assertSee('Dostosuj')
        ->assertSee('Akceptuję wszystkie')
        ->assertSee('Ustawienia cookies')
        ->assertSee('@etb:open-cookie-settings.window="reopenBanner()"', false)
        ->assertSee(route('cookies.policy'), false);
});

it('publishes a comprehensive cookie policy', function () {
    $response = $this->get(route('cookies.policy'));

    $response->assertOk()
        ->assertSee('Polityka cookies')
        ->assertSee('Niezbędne')
        ->assertSee('Funkcjonalne')
        ->assertSee('Analityczne')
        ->assertSee('Marketingowe i multimedia zewnętrzne')
        ->assertSee(config('session.cookie'))
        ->assertSee('etb_cookie_consent')
        ->assertSee('_ga oraz _ga_*')
        ->assertSee('Google Analytics 4')
        ->assertSee('art. 399–400')
        ->assertSee('Otwórz ustawienia cookies');
});

it('does not load a youtube iframe before marketing consent', function () {
    $author = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $news = News::query()->create([
        'type' => News::TYPE_VIDEO,
        'title' => 'Materiał chroniony zgodą',
        'content' => 'Opis materiału.',
        'excerpt' => 'Opis materiału.',
        'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        'author_id' => $author->id,
        'publish_at' => now()->subMinute(),
        'is_visible' => true,
    ]);

    $response = $this->get(route('news.show', $news));

    $response
        ->assertOk()
        ->assertSee('data-cookie-category="marketing"', false)
        ->assertSee('data-cookie-src="https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ"', false)
        ->assertDontSee('src="https://www.youtube.com/embed/dQw4w9WgXcQ"', false)
        ->assertSee('Film YouTube jest zablokowany');

    expect($response->getContent())->not->toMatch('/<iframe[^>]+\ssrc="/i');
});

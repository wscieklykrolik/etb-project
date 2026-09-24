<?php

use App\Models\AppSetting;
use App\Models\ImportantPage;
use App\Models\News;
use App\Models\User;
use Database\Seeders\ContentSeeder;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\DB;

it('preserves long Polish content and URLs accepted by forms', function () {
    $body = str_repeat('Zażółć gęślą jaźń. ', 6000);
    $url = 'https://example.com/'.str_repeat('a', 1800);
    AppSetting::setValue('tickets_page_body', $body);
    ImportantPage::create(['slug' => 'regulamin', 'body' => $body]);
    $user = User::factory()->create();
    $news = News::create(['title' => 'Aktualność', 'content' => $body, 'author_id' => $user->id]);
    $sponsorId = DB::table('sponsors')->insertGetId([
        'name' => 'Partner', 'type' => 'partner', 'url' => $url, 'logo_path' => 'sponsors/test.jpg',
    ]);
    $tournamentId = DB::table('three_x_three_tournaments')->insertGetId([
        'name' => 'Turniej', 'date' => '2026-10-01', 'location' => 'Łódź', 'registration_url' => $url,
    ]);
    $matchId = DB::table('matches')->insertGetId([
        'opponent_name' => 'Drużyna', 'match_date' => '2026-10-01 12:00:00', 'location' => 'Łódź', 'ticket_url' => $url,
    ]);
    expect(AppSetting::getValue('tickets_page_body'))->toBe($body)
        ->and(ImportantPage::where('slug', 'regulamin')->value('body'))->toBe($body)
        ->and($news->fresh()->content)->toBe($body)
        ->and(DB::table('sponsors')->where('id', $sponsorId)->value('url'))->toBe($url)
        ->and(DB::table('three_x_three_tournaments')->where('id', $tournamentId)->value('registration_url'))->toBe($url)
        ->and(DB::table('matches')->where('id', $matchId)->value('ticket_url'))->toBe($url);
});

it('refuses demo seeders in production before writing data', function (string $seeder) {
    app()->instance('env', 'production');
    try {
        (new $seeder)->run();
        $this->fail('Seeder powinien odmówić wykonania.');
    } catch (RuntimeException $exception) {
        expect($exception->getMessage())->toContain('wyłącznie lokalnie');
        expect(User::count())->toBe(0);
    }
})->with([DatabaseSeeder::class, ContentSeeder::class]);

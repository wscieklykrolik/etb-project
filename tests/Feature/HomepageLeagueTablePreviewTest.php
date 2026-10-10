<?php

use App\Models\AppSetting;
use App\Models\LeagueStanding;
use App\Models\LeagueTableSnapshot;
use App\Models\Opponent;
use App\Models\User;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createHomepageStanding(string $name, int $position): LeagueStanding
{
    $opponent = Opponent::query()->create([
        'name' => $name,
        'is_league_team' => true,
    ]);

    return LeagueStanding::query()->create([
        'opponent_id' => $opponent->id,
        'league_id' => 215,
        'season' => '2025/2026',
        'position' => $position,
        'points' => 20 - $position,
        'games' => 10,
        'wins' => 10 - $position,
        'losses' => $position,
        'home_wins' => 4,
        'home_losses' => 1,
        'away_wins' => 3,
        'away_losses' => 2,
        'points_for' => 800,
        'points_against' => 650,
        'points_difference' => 150,
        'ratio' => 1.2308,
        'source_team_name' => $name,
        'synced_at' => now(),
    ]);
}

it('does not show the league preview before a table has been fetched', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertDontSee('Aktualna pozycja');
});

it('shows our team and the teams directly around it', function () {
    createHomepageStanding('Drużyna pierwsza', 1);
    createHomepageStanding('Jaszczak.Finanse ETB Łódź', 2);
    createHomepageStanding('Drużyna trzecia', 3);
    createHomepageStanding('Drużyna czwarta', 4);

    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertSee('Aktualna pozycja')
        ->assertSeeInOrder(['Drużyna pierwsza', 'Jaszczak.Finanse ETB Łódź', 'Drużyna trzecia'])
        ->assertDontSee('Drużyna czwarta')
        ->assertSee('Zobacz pełną tabelę');
});

it('uses the most recently updated published table by default', function () {
    $current = createHomepageStanding('Jaszczak.Finanse ETB Łódź', 1);
    $current->update(['synced_at' => now()->subDay()]);

    $snapshot = LeagueTableSnapshot::query()->create([
        'title' => 'Najnowsza tabela ręczna',
        'season' => '2026/27',
        'is_published' => true,
    ]);
    $snapshot->rows()->createMany([
        ['position' => 1, 'team_name' => 'Rywal A', 'points' => 8, 'games' => 5, 'wins' => 3, 'losses' => 2],
        ['position' => 2, 'team_name' => 'Jaszczak.Finanse ETB Łódź', 'points' => 7, 'games' => 5, 'wins' => 2, 'losses' => 3],
        ['position' => 3, 'team_name' => 'Rywal B', 'points' => 6, 'games' => 5, 'wins' => 1, 'losses' => 4],
    ]);

    $this->get(route('home'))
        ->assertSee('Najnowsza tabela ręczna — sezon 2026/27')
        ->assertSeeInOrder(['Rywal A', 'Jaszczak.Finanse ETB Łódź', 'Rywal B']);
});

it('shows two teams below the leader and two teams above the last team', function () {
    foreach (['Jaszczak.Finanse ETB Łódź', 'Druga', 'Trzecia', 'Czwarta'] as $index => $name) {
        createHomepageStanding($name, $index + 1);
    }

    $this->get(route('home'))
        ->assertSeeInOrder(['Jaszczak.Finanse ETB Łódź', 'Druga', 'Trzecia'])
        ->assertDontSee('Czwarta');

    Opponent::query()->where('name', 'Jaszczak.Finanse ETB Łódź')->update(['name' => 'Lider Alfa']);
    Opponent::query()->where('name', 'Czwarta')->update(['name' => 'Jaszczak.Finanse ETB Łódź']);

    $this->get(route('home'))
        ->assertDontSee('Lider Alfa')
        ->assertSeeInOrder(['Druga', 'Trzecia', 'Jaszczak.Finanse ETB Łódź']);
});

it('lets an administrator save the homepage table source', function () {
    createHomepageStanding('Jaszczak.Finanse ETB Łódź', 1);
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

    $this->actingAs($admin)
        ->put(route('admin.league-table.homepage-preview.update'), ['source' => 'current:lzkosz'])
        ->assertRedirect();

    expect(AppSetting::getValue('homepage_league_table_source'))->toBe('current:lzkosz');
});

it('schedules an automatic table refresh every day at 16:00 Warsaw time', function () {
    $event = collect(app(Schedule::class)->events())
        ->first(fn ($event): bool => str_contains($event->command ?? '', 'lzkosz:sync-table'));

    expect($event)->not->toBeNull()
        ->and($event->expression)->toBe('0 16 * * *')
        ->and($event->timezone)->toBe('Europe/Warsaw');
});

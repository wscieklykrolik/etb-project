<?php

use App\Exceptions\LeagueTableSourceException;
use App\Models\AppSetting;
use App\Models\LeagueStanding;
use App\Models\LeagueTableSnapshot;
use App\Models\User;
use App\Services\LzkoszLeagueTableService;
use App\Services\SofascoreLeagueTableService;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\Http;

// Representative response contracts; live access must also be verified on the hosting account.
function sofaStandingsFixture(): array
{
    return ['standings' => [['rows' => [
        ['position' => 1, 'team' => ['id' => 101, 'name' => 'ETB Łódź'], 'matches' => 6, 'wins' => 3, 'losses' => 3, 'scoresFor' => 420, 'scoresAgainst' => 400, 'scoreDiff' => 20, 'points' => 6],
        ['position' => 2, 'team' => ['id' => 102, 'name' => 'Przeciwnik testowy'], 'matches' => 6, 'wins' => 3, 'losses' => 3, 'scoreDiff' => -20, 'points' => 6],
    ]]]];
}

function sofaEventFixture(int $id, int $winner = 1): array
{
    return [
        'id' => $id,
        'startTimestamp' => 1700000000 + $id * 86400,
        'status' => ['type' => 'finished'],
        'season' => ['id' => 107625],
        'tournament' => ['uniqueTournament' => ['id' => 39654]],
        'winnerCode' => $winner,
        'homeTeam' => ['id' => 101, 'name' => 'ETB Łódź'],
        'awayTeam' => ['id' => 102, 'name' => 'Przeciwnik testowy'],
    ];
}

function fakeSofaImport(): void
{
    Http::preventStrayRequests();
    Http::fake([
        SofascoreLeagueTableService::API_URL.'/standings/total' => Http::response(sofaStandingsFixture()),
        SofascoreLeagueTableService::API_URL.'/events/last/0' => Http::response([
            'events' => [sofaEventFixture(6, 2), sofaEventFixture(5), sofaEventFixture(4, 2)],
            'hasNextPage' => true,
        ]),
        SofascoreLeagueTableService::API_URL.'/events/last/1' => Http::response([
            'events' => [sofaEventFixture(4, 2), sofaEventFixture(3), sofaEventFixture(2, 2), sofaEventFixture(1)],
            'hasNextPage' => false,
        ]),
    ]);
}

it('offers three sources and imports Sofascore with DIFF and the latest five results', function () {
    fakeSofaImport();
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $this->actingAs($admin)->get(route('profile.edit', ['section' => 'league-table']))->assertOk()
        ->assertSee('value="lzkosz"', false)->assertSee('value="kpzkosz"', false)->assertSee('value="sofascore"', false);
    $this->post(route('admin.league-table.sync'), ['source' => 'sofascore'])->assertSessionHasNoErrors()->assertSessionHas('success');

    $standing = LeagueStanding::query()->where('source_team_name', 'ETB Łódź')->firstOrFail();
    expect($standing->league_id)->toBe(39654)
        ->and($standing->home_wins)->toBeNull()
        ->and($standing->ratio)->toBeNull()
        ->and($standing->points_difference)->toBe(20)
        ->and(array_column($standing->last_five, 'result'))->toBe(['L', 'W', 'L', 'W', 'L'])
        ->and(AppSetting::getValue('league_table_source'))->toBe('sofascore');

    foreach (['schedule.table', 'schedule'] as $route) {
        $this->get(route($route))->assertOk()->assertSee('Różnica (DIFF)')->assertSee('+20')
            ->assertSee('Ostatnie 5 meczów')->assertSee('Zwycięstwo')->assertSee('Porażka')
            ->assertDontSee('>Dom</th>', false)->assertDontSee('>Wyjazd</th>', false)->assertDontSee('>Stosunek</th>', false)
            ->assertSee(LzkoszLeagueTableService::SOURCES['sofascore']['url'], false);
    }
    $this->get(route('home'))->assertOk()->assertSee('ETB Łódź');
    $this->get(route('profile.edit', ['section' => 'league-table']))->assertOk()->assertSee('różnica +20');
});

it('hides missing numeric columns and retains real zero values', function () {
    $data = sofaStandingsFixture();
    $data['standings'][0]['rows'] = [$data['standings'][0]['rows'][0]];
    unset($data['standings'][0]['rows'][0]['points'], $data['standings'][0]['rows'][0]['scoresFor'], $data['standings'][0]['rows'][0]['scoresAgainst']);
    $data['standings'][0]['rows'][0]['scoreDiff'] = 0;
    Http::fake([
        SofascoreLeagueTableService::API_URL.'/standings/total' => Http::response($data),
        SofascoreLeagueTableService::API_URL.'/events/last/0' => Http::response(['events' => [], 'hasNextPage' => false]),
    ]);
    $this->artisan('lzkosz:sync-table', ['source' => 'sofascore'])->assertSuccessful();
    expect(LeagueStanding::query()->first()->points)->toBeNull();
    $this->get(route('schedule.table'))->assertOk()->assertDontSee('>Pkt</th>', false)->assertDontSee('>Kosze</th>', false)
        ->assertSee('Różnica (DIFF)')->assertDontSee('Ostatnie 5 meczów');
    $this->get(route('home'))->assertOk()->assertDontSee('>Pkt</span>', false)->assertSee('Mecze');
});

it('excludes cancelled unfinished and other season events from the form', function () {
    $service = app(SofascoreLeagueTableService::class);
    $events = collect([
        sofaEventFixture(1),
        array_replace_recursive(sofaEventFixture(2), ['status' => ['type' => 'inprogress']]),
        array_replace_recursive(sofaEventFixture(3), ['status' => ['type' => 'canceled']]),
        array_replace_recursive(sofaEventFixture(4), ['season' => ['id' => 1]]),
        array_replace_recursive(sofaEventFixture(5), ['tournament' => ['uniqueTournament' => ['id' => 2]]]),
        sofaEventFixture(6, 2),
    ]);
    $rows = $service->withForm($service->parse(sofaStandingsFixture()), $events);
    expect(array_column($rows[0]['last_five'], 'result'))->toBe(['W', 'L'])
        ->and(array_column($rows[1]['last_five'], 'result'))->toBe(['L', 'W']);
});

it('preserves the published data and source when Sofascore rejects the request', function (int $status) {
    fakeSofaImport();
    app(LzkoszLeagueTableService::class)->sync('sofascore');
    $before = LeagueStanding::query()->get()->toArray();
    Http::swap((new Factory)->fake(['*' => Http::response([], $status)]));
    AppSetting::setValue('league_table_source', 'kpzkosz');
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $this->actingAs($admin)->post(route('admin.league-table.sync'), ['source' => 'sofascore'])
        ->assertSessionHas('error', fn ($message) => str_contains($message, 'HTTP '.$status));
    expect(LeagueStanding::query()->get()->toArray())->toBe($before)
        ->and(AppSetting::getValue('league_table_source'))->toBe('kpzkosz');
})->with([403, 429]);

it('archives optional statistics the form and source attribution without inventing values', function () {
    fakeSofaImport();
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $this->actingAs($admin)->post(route('admin.league-table.sync'), ['source' => 'sofascore']);
    $this->post(route('admin.league-table.archive'), ['title' => 'Archiwum Sofascore', 'is_published' => true])->assertSessionHas('success');
    $snapshot = LeagueTableSnapshot::query()->firstOrFail();
    expect($snapshot->source_label)->toBe('Sofascore')
        ->and($snapshot->rows->first()->ratio)->toBeNull()
        ->and($snapshot->rows->first()->last_five)->toHaveCount(5);
    $this->get(route('schedule.table'))->assertOk()->assertSee('Archiwum Sofascore')->assertDontSee('>Stosunek</th>', false);
    $this->get(route('profile.edit', ['section' => 'league-table']))->assertOk()->assertSee('Archiwum Sofascore');
    $manualRows = $snapshot->rows->map(fn ($row) => implode(';', [$row->team_name, $row->points, $row->games, $row->wins, $row->losses, $row->home_wins, $row->home_losses, $row->away_wins, $row->away_losses, $row->points_for, $row->points_against]))->implode("\n");
    $this->put(route('admin.league-table.manual.update', $snapshot), ['title' => 'Archiwum Sofascore', 'rows' => $manualRows, 'is_published' => true])->assertSessionHasNoErrors();
    expect($snapshot->fresh()->rows->first()->last_five)->toHaveCount(5)
        ->and($snapshot->fresh()->rows->first()->ratio)->toBeNull()
        ->and($snapshot->fresh()->rows->last()->points_difference)->toBe(-20);
});

it('rejects malformed or ambiguous standings instead of replacing published data', function (array $data) {
    expect(fn () => app(SofascoreLeagueTableService::class)->parse($data))->toThrow(LeagueTableSourceException::class);
})->with([
    [[]],
    [['standings' => 'bad']],
    [['standings' => [['rows' => []]]]],
    [['standings' => [['rows' => [['position' => 1, 'team' => ['id' => 1]]]]]]],
    [['standings' => [sofaStandingsFixture()['standings'][0], sofaStandingsFixture()['standings'][0]]]],
]);

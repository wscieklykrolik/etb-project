<?php

use App\Models\LeagueStanding;
use App\Models\LeagueTableSnapshot;
use App\Models\Opponent;
use App\Models\User;
use Illuminate\Support\Facades\Http;

it('imports the lzkosz league table into local opponents and standings', function () {
    Http::fake([
        'lzkosz.pl/liga/215/tabela.html' => Http::response(<<<'HTML'
            <table>
                <thead>
                    <tr>
                        <th>m</th><th>drużyna</th><th>pkt</th><th>mecze</th><th>zw. - por.</th>
                        <th>dom</th><th>wyjazd</th><th>pkt. zd. - pkt. str.</th><th>różnica</th><th>stosunek</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td><td><a href="/liga/215/druzyny/d/13647/skk.html">ŚKK </a></td><td>41</td><td>22</td><td>19 - 3</td><td>11 - 0</td><td>8 - 3</td><td>2238 - 1541</td><td>+697</td><td>1.4523</td>
                    </tr>
                    <tr>
                        <td>9</td><td><a href="/liga/215/druzyny/d/13300/profi-sunbud-pkk-99-pabianice.html">PKK 99 </a></td><td>31</td><td>22</td><td>9 - 13</td><td>5 - 6</td><td>4 - 7</td><td>1686 - 1847</td><td>-161</td><td>0.9128</td>
                    </tr>
                </tbody>
            </table>
        HTML),
    ]);

    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

    $response = $this->actingAs($admin)->post(route('admin.league-table.sync'));

    $response->assertRedirect(route('profile.edit'));
    $this->assertDatabaseHas('opponents', [
        'name' => 'PKK 99',
        'source_team_url' => 'https://lzkosz.pl/liga/215/druzyny/d/13300/profi-sunbud-pkk-99-pabianice.html',
    ]);
    $this->assertDatabaseHas('league_standings', [
        'season' => '2025/2026',
        'league_id' => 215,
        'position' => 9,
        'points' => 31,
        'games' => 22,
        'wins' => 9,
        'losses' => 13,
        'points_for' => 1686,
        'points_against' => 1847,
        'points_difference' => -161,
    ]);

    expect(Opponent::query()->count())->toBe(2);
    expect(LeagueStanding::query()->count())->toBe(2);
});

it('imports a selected kpzkosz table with a custom season label', function () {
    Http::fake([
        'www.kpzkosz.com/liga/89/tabela.html' => Http::response(<<<'HTML'
            <table><thead><tr><th>m</th><th>drużyna</th><th>pkt</th><th>mecze</th><th>zw. - por.</th><th>dom</th><th>wyjazd</th><th>kosze</th><th>różnica</th><th>stosunek</th></tr></thead>
            <tbody><tr><td>1</td><td><a href="/liga/89/druzyna/etb.html">ETB Łódź</a></td><td>20</td><td>10</td><td>10 - 0</td><td>5 - 0</td><td>5 - 0</td><td>800 - 650</td><td>+150</td><td>1.2308</td></tr></tbody></table>
        HTML),
    ]);

    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $this->actingAs($admin)->post(route('admin.league-table.sync'), [
        'source' => 'kpzkosz',
        'season' => '2026/27',
    ])->assertRedirect(route('profile.edit'));

    $this->assertDatabaseHas('league_standings', ['league_id' => 89, 'season' => '2026/27', 'points' => 20]);
    $this->assertDatabaseHas('opponents', ['source_team_url' => 'https://www.kpzkosz.com/liga/89/druzyna/etb.html']);
});

it('shows the requested empty message without the old source description', function () {
    $this->get(route('schedule.table'))
        ->assertOk()
        ->assertSee('Tabela nie została jeszcze opublikowana')
        ->assertDontSee('Tabela 3 Ligi Mężczyzn pobierana z ŁZKosz');
});

it('publishes a manually entered additional table only when it has rows', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

    $this->actingAs($admin)->post(route('admin.league-table.manual.store'), [
        'title' => 'Turniej zagraniczny',
        'season' => '2026/27',
        'description' => 'Tabela turniejowa.',
        'rows' => '',
        'is_published' => '1',
    ])->assertRedirect();

    expect(LeagueTableSnapshot::query()->first()->is_published)->toBeFalse();
    $this->get(route('schedule.table'))->assertDontSee('Turniej zagraniczny');

    $snapshot = LeagueTableSnapshot::query()->first();
    $this->actingAs($admin)->put(route('admin.league-table.manual.update', $snapshot), [
        'title' => 'Turniej zagraniczny',
        'season' => '2026/27',
        'description' => 'Tabela turniejowa.',
        'rows' => 'ETB Łódź;20;10;10;0;5;0;5;0;800;650',
        'is_published' => '1',
    ])->assertRedirect();

    $this->get(route('schedule.table'))
        ->assertOk()
        ->assertSee('Turniej zagraniczny')
        ->assertSee('ETB Łódź');
});

it('shows the configured kpzkosz league link next to the permanent link', function () {
    $this->get(route('schedule.third-league'))
        ->assertOk()
        ->assertSee('https://www.lzkosz.pl/liga/215.html', false)
        ->assertSee('https://www.kpzkosz.com/liga/89.html', false)
        ->assertSee('Otwórz ligę w KPZKosz');
});

it('archives the current table together with team names and logo paths', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $opponent = Opponent::query()->create([
        'name' => 'ETB Łódź',
        'is_league_team' => true,
        'logo_path' => 'team-logos/etb.png',
    ]);
    LeagueStanding::query()->create([
        'opponent_id' => $opponent->id,
        'league_id' => 215,
        'season' => '2025/2026',
        'position' => 1,
        'points' => 20,
        'games' => 10,
        'wins' => 10,
        'losses' => 0,
        'home_wins' => 5,
        'home_losses' => 0,
        'away_wins' => 5,
        'away_losses' => 0,
        'points_for' => 800,
        'points_against' => 650,
        'points_difference' => 150,
        'ratio' => 1.2308,
    ]);

    $this->actingAs($admin)->post(route('admin.league-table.archive'), [
        'title' => 'Archiwum ligi',
        'season' => '2025/26',
        'is_published' => '1',
    ])->assertRedirect();

    $snapshot = LeagueTableSnapshot::query()->with('rows')->firstOrFail();
    expect($snapshot->is_published)->toBeTrue()
        ->and($snapshot->rows->first()->team_name)->toBe('ETB Łódź')
        ->and($snapshot->rows->first()->logo_path)->toBe('team-logos/etb.png');
});

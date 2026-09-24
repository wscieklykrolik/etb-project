<?php

use App\Models\Player;
use App\Models\TeamStaff;
use App\Models\ThreeXThreeMember;
use App\Models\User;

dataset('people birth year', [
    'player' => ['/players', Player::class, ['first_name' => 'Jan', 'last_name' => 'Żak', 'number' => 1, 'position' => 'point_guard']],
    'staff' => ['/admin/staff', TeamStaff::class, ['name' => 'Jan Żak', 'role' => 'Trener']],
    '3x3 player' => ['/admin/3x3/members', ThreeXThreeMember::class, ['name' => 'Jan Żak', 'role' => 'Zawodnik', 'is_coach' => false]],
    '3x3 coach' => ['/admin/3x3/members', ThreeXThreeMember::class, ['name' => 'Jan Żak', 'role' => 'Trener', 'is_coach' => true]],
]);

beforeEach(function () {
    $this->actingAs(User::factory()->create(['role' => User::ROLE_ADMIN]));
});

it('creates people with a year, an empty year or no year field', function ($uri, $model, $data) {
    foreach ([['birth_year' => '2003'], ['birth_year' => ''], []] as $extra) {
        $this->post($uri, array_merge($data, $extra))->assertSessionHasNoErrors()->assertRedirect();
        $person = $model::query()->latest('id')->firstOrFail();
        expect($person->birth_year)->toBe(($extra['birth_year'] ?? '') === '2003' ? 2003 : null);
        if ($person instanceof Player) {
            expect($person->date_of_birth)->toBeNull();
        }
    }
    expect($model::count())->toBe(3);
})->with('people birth year');

it('updates and clears a persons year', function ($uri, $model, $data) {
    $person = $model::create(array_merge($data, ['birth_year' => 2003]));
    $this->put($uri.'/'.$person->id, array_merge($data, ['birth_year' => '2004']))
        ->assertSessionHasNoErrors()->assertRedirect();
    expect($person->fresh()->birth_year)->toBe(2004);
    $this->put($uri.'/'.$person->id, array_merge($data, ['birth_year' => '']))
        ->assertSessionHasNoErrors()->assertRedirect();
    expect($person->fresh()->birth_year)->toBeNull();
})->with('people birth year');

it('rejects invalid years on creation and editing', function ($uri, $model, $data) {
    $person = $model::create($data);
    foreach (['2003-01-01', 'abc', '2003.5', 1899, now()->year + 1] as $year) {
        $this->post($uri, array_merge($data, ['birth_year' => $year]))->assertSessionHasErrors('birth_year');
        $this->put($uri.'/'.$person->id, array_merge($data, ['birth_year' => $year]))->assertSessionHasErrors('birth_year');
    }
    expect($model::count())->toBe(1);
    expect($person->fresh()->birth_year)->toBeNull();
})->with('people birth year');

it('preserves a matching legacy date and clears it when the year changes or is removed', function ($year, $date) {
    $data = ['first_name' => 'Jan', 'last_name' => 'Żak', 'number' => 1, 'position' => 'point_guard'];
    $player = Player::create(array_merge($data, ['date_of_birth' => '2003-07-15', 'birth_year' => 2003]));
    $this->put(route('players.update', $player), array_merge($data, ['birth_year' => $year]))
        ->assertSessionHasNoErrors()->assertRedirect();
    expect($player->fresh()->date_of_birth?->format('Y-m-d'))->toBe($date);
})->with([
    [2003, '2003-07-15'],
    [2004, null],
    ['', null],
]);

it('backfills years without changing existing dates', function () {
    $migration = require database_path('migrations/2026_09_24_120000_add_optional_birth_year_to_team_members.php');
    $migration->down();
    $player = Player::create([
        'first_name' => 'Jan', 'last_name' => 'Żak', 'number' => 1,
        'position' => 'point_guard', 'date_of_birth' => '2003-07-15',
    ]);
    $migration->up();
    expect($player->fresh()->birth_year)->toBe(2003);
    expect($player->fresh()->date_of_birth->format('Y-m-d'))->toBe('2003-07-15');
});

it('renders optional year inputs and preserves an existing players year', function () {
    $player = Player::create([
        'first_name' => 'Jan', 'last_name' => 'Żak', 'number' => 1,
        'position' => 'point_guard', 'birth_year' => 2003,
    ]);
    $this->get(route('players.edit', $player))->assertOk()
        ->assertSee('Rok urodzenia (opcjonalnie)')
        ->assertSee('name="birth_year"', false)
        ->assertSee('value="2003"', false)
        ->assertDontSee('name="date_of_birth"', false);
    $html = view('profile.partials.media-card-form-fields')->render();
    expect($html)->toContain('Rok urodzenia (opcjonalnie)', 'name="birth_year"');
});

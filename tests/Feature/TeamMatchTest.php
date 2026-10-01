<?php

use App\Models\TeamMatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $this->employee = User::factory()->create(['role' => User::ROLE_EMPLOYEE]);
    $this->athlete = User::factory()->create(['role' => User::ROLE_ATHLETE]);
    $this->fan = User::factory()->create(['role' => User::ROLE_FAN]);
});

it('shows public match list', function () {
    TeamMatch::factory()->count(3)->create([
        'status' => TeamMatch::STATUS_UPCOMING,
        'publish_at' => null,
    ]);

    $response = $this->get(route('matches.index'));

    $response->assertOk();
});

it('shows a published match', function () {
    $match = TeamMatch::factory()->create([
        'status' => TeamMatch::STATUS_UPCOMING,
        'publish_at' => null,
    ]);

    $response = $this->get(route('matches.show', $match));

    $response->assertOk();
});

it('lets admin create a match', function () {
    $response = $this->actingAs($this->admin)
        ->post(route('matches.store'), [
            'status' => TeamMatch::STATUS_UPCOMING,
            'opponent_name' => 'Test Team',
            'match_date' => now()->addDays(30)->format('Y-m-d H:i:s'),
            'location' => 'Test Arena',
            'is_home' => true,
        ]);

    $response->assertRedirect(route('profile.edit'));
    $this->assertDatabaseHas('matches', ['opponent_name' => 'Test Team']);
});

it('lets admin create a match without a start time', function () {
    $matchDate = now()->addDays(30)->format('Y-m-d');

    $this->actingAs($this->admin)
        ->post(route('matches.store'), [
            'status' => TeamMatch::STATUS_UPCOMING,
            'opponent_name' => 'Rywal bez godziny',
            'match_date' => $matchDate,
            'match_time' => '',
            'location' => 'Hala testowa',
            'is_home' => true,
        ])
        ->assertRedirect(route('profile.edit'));

    $match = TeamMatch::query()->where('opponent_name', 'Rywal bez godziny')->firstOrFail();

    expect($match->has_time)->toBeFalse()
        ->and($match->timeLabel())->toBe('- : -')
        ->and($match->isUpcoming())->toBeTrue();

    $this->get(route('schedule'))
        ->assertOk()
        ->assertSee('Rywal bez godziny')
        ->assertSee('- : -');
});

it('lets employee create a match', function () {
    $response = $this->actingAs($this->employee)
        ->post(route('matches.store'), [
            'status' => TeamMatch::STATUS_UPCOMING,
            'opponent_name' => 'Employee Match',
            'match_date' => now()->addDays(15)->format('Y-m-d H:i:s'),
            'location' => 'Employee Arena',
            'is_home' => false,
        ]);

    $response->assertRedirect(route('profile.edit'));
    $this->assertDatabaseHas('matches', ['opponent_name' => 'Employee Match']);
});

it('prevents fan from creating a match', function () {
    $response = $this->actingAs($this->fan)
        ->post(route('matches.store'), [
            'status' => TeamMatch::STATUS_UPCOMING,
            'opponent_name' => 'Fan Match',
            'match_date' => now()->addDays(10)->format('Y-m-d H:i:s'),
            'location' => 'Fan Arena',
            'is_home' => true,
        ]);

    $response->assertForbidden();
});

it('lets admin update a match', function () {
    $match = TeamMatch::factory()->create(['opponent_name' => 'Old Name']);

    $response = $this->actingAs($this->admin)
        ->put(route('matches.update', $match), [
            'status' => TeamMatch::STATUS_UPCOMING,
            'opponent_name' => 'New Name',
            'match_date' => now()->addDays(20)->format('Y-m-d H:i:s'),
            'location' => 'Updated Arena',
            'is_home' => true,
        ]);

    $response->assertRedirect(route('profile.edit'));
    $this->assertDatabaseHas('matches', ['opponent_name' => 'New Name']);
    $this->assertDatabaseMissing('matches', ['opponent_name' => 'Old Name']);
});

it('lets admin delete a match', function () {
    $match = TeamMatch::factory()->create();

    $response = $this->actingAs($this->admin)
        ->delete(route('matches.destroy', $match));

    $response->assertRedirect(route('profile.edit'));
    $this->assertDatabaseMissing('matches', ['id' => $match->id]);
});

it('validates required fields on store', function () {
    $response = $this->actingAs($this->admin)
        ->post(route('matches.store'), []);

    $response->assertSessionHasErrors(['opponent_name', 'match_date', 'location', 'status']);
});

it('validates future date for upcoming matches', function () {
    $response = $this->actingAs($this->admin)
        ->post(route('matches.store'), [
            'status' => TeamMatch::STATUS_UPCOMING,
            'opponent_name' => 'Past Match',
            'match_date' => now()->subDays(1)->format('Y-m-d H:i:s'),
            'location' => 'Past Arena',
            'is_home' => true,
        ]);

    $response->assertSessionHasErrors(['match_date']);
});

it('requires scores for finished matches', function () {
    $response = $this->actingAs($this->admin)
        ->post(route('matches.store'), [
            'status' => TeamMatch::STATUS_FINISHED,
            'opponent_name' => 'Finished Match',
            'match_date' => now()->subDays(5)->format('Y-m-d H:i:s'),
            'location' => 'Done Arena',
            'our_score' => null,
            'opponent_score' => null,
            'is_home' => true,
        ]);

    $response->assertSessionHasErrors(['our_score', 'opponent_score']);
});

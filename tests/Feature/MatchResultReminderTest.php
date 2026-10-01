<?php

use App\Models\MatchResultReminderDismissal;
use App\Models\TeamMatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    Carbon::setTestNow('2026-10-01 18:00:00');
    $this->admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
});

afterEach(function () {
    Carbon::setTestNow();
});

it('automatically finishes an upcoming match two hours after its start', function () {
    $duringMatch = TeamMatch::factory()->create([
        'status' => TeamMatch::STATUS_UPCOMING,
        'match_date' => now()->subHour(),
        'our_score' => null,
        'opponent_score' => null,
    ]);
    $afterMatch = TeamMatch::factory()->create([
        'status' => TeamMatch::STATUS_UPCOMING,
        'match_date' => now()->subHours(2),
        'our_score' => null,
        'opponent_score' => null,
    ]);

    expect($duringMatch->isUpcoming())->toBeTrue()
        ->and($duringMatch->statusLabel())->toBe('Nadchodzący')
        ->and($afterMatch->isFinished())->toBeTrue()
        ->and($afterMatch->statusLabel())->toBe('Zakończony')
        ->and($afterMatch->resultLabel())->toBe('Wynik nieuzupełniony');
});

it('renders only one status label on a public match card', function () {
    TeamMatch::factory()->create([
        'status' => TeamMatch::STATUS_UPCOMING,
        'match_date' => now()->subHours(3),
        'our_score' => null,
        'opponent_score' => null,
        'publish_at' => null,
    ]);

    $content = $this->get(route('schedule'))->assertOk()->getContent();

    expect(substr_count($content, '>Zakończony</p>'))->toBe(1);
});

it('shows an admin a result reminder for an automatically finished match', function () {
    $match = TeamMatch::factory()->create([
        'opponent_name' => 'Testowy Rywal',
        'status' => TeamMatch::STATUS_UPCOMING,
        'match_date' => now()->subHours(3),
        'our_score' => null,
        'opponent_score' => null,
    ]);

    $this->actingAs($this->admin)
        ->get(route('profile.edit'))
        ->assertOk()
        ->assertSee('Mecz został zakończony — dodaj wynik')
        ->assertSee('Testowy Rywal')
        ->assertSee(route('admin.matches.result-reminder.store', $match), false);
});

it('snoozes a result reminder only for the next panel request', function () {
    $match = TeamMatch::factory()->create([
        'status' => TeamMatch::STATUS_UPCOMING,
        'match_date' => now()->subHours(3),
        'our_score' => null,
        'opponent_score' => null,
    ]);

    $this->actingAs($this->admin)
        ->post(route('admin.matches.result-reminder.remind', $match))
        ->assertRedirect(route('profile.edit'))
        ->assertSessionHas('info');

    $this->get(route('profile.edit'))
        ->assertOk()
        ->assertDontSee('Mecz został zakończony — dodaj wynik');

    $this->get(route('profile.edit'))
        ->assertOk()
        ->assertSee('Mecz został zakończony — dodaj wynik');
});

it('permanently dismisses a result reminder for the current admin', function () {
    $match = TeamMatch::factory()->create([
        'status' => TeamMatch::STATUS_UPCOMING,
        'match_date' => now()->subHours(3),
        'our_score' => null,
        'opponent_score' => null,
    ]);

    $this->actingAs($this->admin)
        ->post(route('admin.matches.result-reminder.dismiss', $match))
        ->assertRedirect(route('profile.edit'));

    expect(MatchResultReminderDismissal::query()
        ->where('user_id', $this->admin->id)
        ->where('match_id', $match->id)
        ->exists())->toBeTrue();

    $this->get(route('profile.edit'))
        ->assertOk()
        ->assertDontSee('Mecz został zakończony — dodaj wynik');
});

it('saves the score directly from the reminder', function () {
    $match = TeamMatch::factory()->create([
        'status' => TeamMatch::STATUS_UPCOMING,
        'match_date' => now()->subHours(3),
        'our_score' => null,
        'opponent_score' => null,
    ]);

    $this->actingAs($this->admin)
        ->post(route('admin.matches.result-reminder.store', $match), [
            'our_score' => 91,
            'opponent_score' => 65,
        ])
        ->assertRedirect(route('profile.edit', ['section' => 'matches']))
        ->assertSessionHas('success', 'Wynik meczu został zapisany.');

    $this->assertDatabaseHas('matches', [
        'id' => $match->id,
        'status' => TeamMatch::STATUS_FINISHED,
        'our_score' => 91,
        'opponent_score' => 65,
    ]);
});

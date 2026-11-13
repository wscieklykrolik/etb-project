<?php

use App\Models\AppSetting;
use App\Models\TeamMatch;

it('renders ETB first with its logo for home and away matches', function (bool $isHome) {
    AppSetting::setValue('club_logo', 'logos/etb.png');
    AppSetting::setValue('public_team_name', 'ETB Sponsor Testowy');
    $match = TeamMatch::factory()->create([
        'is_home' => $isHome,
        'status' => TeamMatch::STATUS_UPCOMING,
        'match_date' => now()->addWeek(),
        'our_score' => null,
        'opponent_score' => null,
        'opponent_name' => 'Rywal',
        'opponent_logo' => 'logos/rywal.png',
        'home_logo' => null,
        'publish_at' => null,
        'include_in_lzkosz' => true,
        'lzkosz_round' => TeamMatch::LZKOSZ_ROUND_ONE,
    ]);

    foreach ([route('home'), route('schedule'), route('schedule.matches.show', $match)] as $url) {
        $this->get($url)
            ->assertOk()
            ->assertSee('ETB Sponsor Testowy')
            ->assertSeeInOrder(['data-team="etb"', 'logos/etb.png', 'data-team="opponent"', 'logos/rywal.png'], false);
    }
})->with([true, false]);

it('shows the score between the teams on public match cards', function (int $ourScore, int $opponentScore, string $result) {
    TeamMatch::factory()->create([
        'status' => TeamMatch::STATUS_FINISHED,
        'match_date' => now()->subDay(),
        'our_score' => $ourScore,
        'opponent_score' => $opponentScore,
        'publish_at' => null,
    ]);

    $score = "{$ourScore}:{$opponentScore}";

    foreach ([route('home'), route('schedule')] as $url) {
        $this->get($url)
            ->assertOk()
            ->assertSeeInOrder(['data-team="etb"', $score, $result, 'data-team="opponent"'], false)
            ->assertDontSee('aria-label="kontra"', false);
    }
})->with([
    [102, 95, 'Zwycięstwo'],
    [87, 106, 'Porażka'],
]);

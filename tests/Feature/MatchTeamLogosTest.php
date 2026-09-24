<?php

use App\Models\AppSetting;
use App\Models\TeamMatch;

it('renders ETB first with its logo for home and away matches', function (bool $isHome) {
    AppSetting::setValue('club_logo', 'logos/etb.png');
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
            ->assertSeeInOrder(['data-team="etb"', 'logos/etb.png', 'data-team="opponent"', 'logos/rywal.png'], false);
    }
})->with([true, false]);

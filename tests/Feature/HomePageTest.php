<?php

use App\Models\News;
use App\Models\Player;
use App\Models\TeamMatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows homepage sections from published news, visible matches, and starting five players', function () {
    $author = User::factory()->create();

    collect(range(1, 6))->each(function (int $index) use ($author): void {
        News::query()->create([
            'title' => "Aktualnosc {$index}",
            'content' => "Treść aktualności {$index}",
            'excerpt' => "Zajawka {$index}",
            'author_id' => $author->id,
            'publish_at' => now()->subDays($index),
            'is_visible' => true,
        ]);
    });

    News::query()->create([
        'title' => 'Ukryta aktualnosc',
        'content' => 'Nie powinna byc widoczna',
        'author_id' => $author->id,
        'publish_at' => now()->subDay(),
        'is_visible' => false,
    ]);

    TeamMatch::query()->create([
        'opponent_name' => 'LKS Lodz',
        'match_date' => now()->subDays(2),
        'location' => 'Lodz',
        'is_home' => false,
        'our_score' => 81,
        'opponent_score' => 74,
        'status' => TeamMatch::STATUS_FINISHED,
        'publish_at' => now()->subDays(3),
    ]);

    TeamMatch::query()->create([
        'opponent_name' => 'Trefl Sopot',
        'match_date' => now()->addDays(2),
        'location' => 'Hala ETB',
        'is_home' => true,
        'status' => TeamMatch::STATUS_UPCOMING,
        'publish_at' => now()->subDay(),
    ]);

    TeamMatch::query()->create([
        'opponent_name' => 'Slask Wroclaw',
        'match_date' => now()->addDays(5),
        'location' => 'Wroclaw',
        'is_home' => false,
        'status' => TeamMatch::STATUS_UPCOMING,
        'publish_at' => now()->subDay(),
    ]);

    Player::query()->create([
        'first_name' => 'Jan',
        'last_name' => 'Kowalski',
        'number' => 7,
        'position' => 'point_guard',
        'date_of_birth' => '2000-01-01',
        'is_starting_five' => true,
    ]);

    Player::query()->create([
        'first_name' => 'Adam',
        'last_name' => 'Rezerwowy',
        'number' => 12,
        'position' => 'center',
        'date_of_birth' => '1998-01-01',
        'is_starting_five' => false,
    ]);

    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertSee('Aktualnosc 1');
    $response->assertSee('Aktualnosc 5');
    $response->assertDontSee('Ukryta aktualnosc');
    $response->assertSee('LKS Lodz');
    $response->assertSee('Trefl Sopot');
    $response->assertSee('Slask Wroclaw');
    $response->assertSee('Jan Kowalski');
    $response->assertDontSee('Adam Rezerwowy');
    $response->assertSee('Kochasz koszykówkę');
    $response->assertSee('academy-cta-link');
    $response->assertSee('academy-cta-arrow');
    $response->assertSee('group-hover:bg-white');
    $response->assertSee('group-hover:text-black');
    $response->assertSee('stroke-width="3.4"', false);
    $response->assertSee('etb-search-panel');
    $response->assertSee('etb-search-ghost');
    $response->assertSee('focus-within:ring-yellow-400');
    $response->assertDontSee('<datalist', false);
});

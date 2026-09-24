<?php

use App\Models\AppSetting;
use App\Models\MatchGame;
use App\Models\Opponent;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('keeps opponent logos isolated per match even when opponent name is reused', function () {
    Storage::fake('public');

    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

    $firstResponse = $this->actingAs($admin)->post(route('matches.store'), [
        'status' => MatchGame::STATUS_UPCOMING,
        'opponent_name' => 'LKS Lodz',
        'match_date' => now()->addDays(7)->format('Y-m-d H:i:s'),
        'location' => 'Hala ETB',
        'is_home' => '1',
        'opponent_logo' => UploadedFile::fake()->create('lks-first.png', 12, 'image/png'),
    ]);

    $firstResponse->assertRedirect(route('profile.edit'));
    $firstMatch = MatchGame::query()->where('opponent_name', 'LKS Lodz')->firstOrFail();

    $secondResponse = $this->actingAs($admin)->post(route('matches.store'), [
        'status' => MatchGame::STATUS_UPCOMING,
        'opponent_name' => 'LKS Lodz',
        'match_date' => now()->addDays(14)->format('Y-m-d H:i:s'),
        'location' => 'Hala ETB',
        'is_home' => '1',
        'opponent_logo' => UploadedFile::fake()->create('lks-rematch.png', 12, 'image/png'),
    ]);

    $secondResponse->assertRedirect(route('profile.edit'));
    $secondMatch = MatchGame::query()->whereKeyNot($firstMatch->id)->firstOrFail();

    expect($firstMatch->fresh()->opponent_logo)->not->toBeNull()
        ->and($secondMatch->opponent_logo)->not->toBeNull()
        ->and($firstMatch->fresh()->opponent_logo)->not->toBe($secondMatch->opponent_logo);

    Storage::disk('public')->assertExists($firstMatch->fresh()->opponent_logo);
    Storage::disk('public')->assertExists($secondMatch->opponent_logo);
});

it('uses stored team logos when creating a match from a known opponent', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

    Opponent::query()->create([
        'name' => 'PKK 99',
        'logo_path' => 'team-logos/pkk99.png',
    ]);

    AppSetting::setValue('default_home_logo', 'team-logos/etb.png');

    $response = $this->actingAs($admin)->post(route('matches.store'), [
        'status' => MatchGame::STATUS_UPCOMING,
        'opponent_name' => 'PKK 99',
        'match_date' => now()->addDays(7)->format('Y-m-d H:i:s'),
        'location' => 'Hala ETB',
        'is_home' => '1',
    ]);

    $response->assertRedirect(route('profile.edit'));

    $match = MatchGame::query()->where('opponent_name', 'PKK 99')->firstOrFail();

    expect($match->opponent_logo)->toBe('team-logos/pkk99.png')
        ->and($match->home_logo)->toBe('team-logos/etb.png');
});

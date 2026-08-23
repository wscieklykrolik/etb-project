<?php

use App\Models\User;
use Illuminate\Support\Str;

test('profile page is displayed', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get('/profile');

    $response->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $user->refresh();

    $this->assertSame('Test User', $user->name);
    $this->assertSame('test@example.com', $user->email);
    $this->assertNull($user->email_verified_at);
});

test('email verification status is unchanged when the email address is unchanged', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'Test User',
            'email' => $user->email,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $this->assertNotNull($user->refresh()->email_verified_at);
});

test('user can delete their account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->delete('/profile', [
            'password' => 'password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/');

    $this->assertGuest();
    $this->assertNull($user->fresh());
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from('/profile')
        ->delete('/profile', [
            'password' => 'wrong-password',
        ]);

    $response
        ->assertSessionHasErrorsIn('userDeletion', 'password')
        ->assertRedirect('/profile');

    $this->assertNotNull($user->fresh());
});

test('admin panel sidebar follows the requested order and keeps shop summary in profile panel', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

    $response = $this
        ->actingAs($admin)
        ->get(route('profile.edit', ['section' => 'dashboard']));

    $response->assertOk();
    $response->assertSee(route('profile.edit', ['section' => 'shop']), false);

    $content = Str::between($response->getContent(), '<aside', '</aside>');
    $labels = [
        'Pulpit',
        'Historia zmian',
        'Pytania i odpowiedzi',
        'Zarządzanie',
        'Użytkownicy',
        'Klub',
        'Aktualności',
        'Akademia',
        'Sponsorzy',
        'Terminarz',
        'Mecze',
        'Terminarz ŁZKosz',
        'Tabela ligi',
        'Terminarz 3x3',
        'Turnieje 3x3',
        'Skład',
        'Zawodnicy',
        'Sztab szkoleniowy',
        'Drużyna 3x3',
        'Sklep',
        'Podsumowanie sklepu',
        'Zamówienia',
        'Produkty',
        'Kategorie',
        'Filtry sklepu',
        'Profil',
        'Wyloguj',
    ];

    $positions = array_map(fn (string $label): int|false => mb_strpos($content, $label), $labels);

    expect($positions)->not->toContain(false);
    expect($positions)->toEqual(collect($positions)->sort()->values()->all());
});

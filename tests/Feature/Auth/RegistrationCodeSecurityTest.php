<?php

use App\Models\PendingRegistration;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('pending registrations do not expose credentials when serialized', function () {
    $pending = PendingRegistration::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => Hash::make('very-long-test-password'),
        'accepted_terms' => true,
        'accepted_privacy' => true,
        'verification_code' => Hash::make('123456'),
        'code_expires_at' => now()->addMinutes(15),
    ]);

    expect($pending->toArray())
        ->not->toHaveKey('password')
        ->not->toHaveKey('verification_code');
});

test('registration verification code locks after repeated invalid attempts', function () {
    config(['security.registration_code.max_attempts' => 2]);

    PendingRegistration::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => Hash::make('very-long-test-password'),
        'accepted_terms' => true,
        'accepted_privacy' => true,
        'verification_code' => Hash::make('123456'),
        'code_expires_at' => now()->addMinutes(15),
    ]);

    $this->post('/register/verify', [
        'email' => 'test@example.com',
        'code' => '000000',
    ])->assertSessionHasErrors('code');

    $this->post('/register/verify', [
        'email' => 'test@example.com',
        'code' => '000000',
    ])->assertSessionHasErrors('code');

    $this->post('/register/verify', [
        'email' => 'test@example.com',
        'code' => '123456',
    ])->assertSessionHasErrors('code');

    expect(User::where('email', 'test@example.com')->exists())->toBeFalse();
    expect(PendingRegistration::where('email', 'test@example.com')->exists())->toBeFalse();
});

test('verified activation code marks the user email as verified', function () {
    PendingRegistration::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => Hash::make('very-long-test-password'),
        'accepted_terms' => true,
        'accepted_privacy' => true,
        'verification_code' => Hash::make('123456'),
        'code_expires_at' => now()->addMinutes(15),
    ]);

    $this->post('/register/verify', [
        'email' => 'test@example.com',
        'code' => '123456',
    ])->assertRedirect(route('home', absolute: false));

    expect(User::where('email', 'test@example.com')->first()->email_verified_at)->not->toBeNull();
});

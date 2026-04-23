<?php

use App\Models\PendingRegistration;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('registration sends activation code before account creation', function () {
    Mail::fake();

    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'accepted_terms' => '1',
        'accepted_privacy' => '1',
    ]);

    $response->assertRedirect(route('register.verify.notice', ['email' => 'test@example.com']));

    $this->assertGuest();
    expect(User::where('email', 'test@example.com')->exists())->toBeFalse();
    expect(PendingRegistration::where('email', 'test@example.com')->exists())->toBeTrue();

    Mail::assertSentCount(1);
});

test('new users can complete registration with activation code', function () {
    $pending = PendingRegistration::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
        'accepted_terms' => true,
        'accepted_privacy' => true,
        'verification_code' => Hash::make('123456'),
        'code_expires_at' => now()->addMinutes(15),
    ]);

    $response = $this->post('/register/verify', [
        'email' => $pending->email,
        'code' => '123456',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
    expect(User::where('email', 'test@example.com')->exists())->toBeTrue();
    expect(PendingRegistration::where('email', 'test@example.com')->exists())->toBeFalse();
});

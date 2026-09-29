<?php

use App\Mail\ActivationCodeMail;
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
        'password' => 'very-long-test-password',
        'password_confirmation' => 'very-long-test-password',
        'accepted_terms' => '1',
        'accepted_privacy' => '1',
    ]);

    $response->assertRedirect(route('register.verify.notice', ['email' => 'test@example.com']));

    $this->assertGuest();
    expect(User::where('email', 'test@example.com')->exists())->toBeFalse();
    expect(PendingRegistration::where('email', 'test@example.com')->exists())->toBeTrue();

    Mail::assertSent(ActivationCodeMail::class, function (ActivationCodeMail $mail): bool {
        $pending = PendingRegistration::where('email', 'test@example.com')->firstOrFail();

        return $mail->hasTo('test@example.com')
            && $mail->ttlMinutes === 10
            && Hash::check($mail->code, $pending->verification_code);
    });
});

test('activation email renders the code and configured validity time', function () {
    $message = new ActivationCodeMail('123456', 10);

    expect($message->render())
        ->toContain('123456')
        ->toContain('10 min')
        ->toContain('Jeśli to nie Ty zakładasz konto');
});

test('pending registration can request a new activation code', function () {
    Mail::fake();

    $pending = PendingRegistration::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => Hash::make('very-long-test-password'),
        'accepted_terms' => true,
        'accepted_privacy' => true,
        'verification_code' => Hash::make('123456'),
        'verification_attempts' => 3,
        'code_expires_at' => now()->addMinutes(5),
    ]);

    $this->post(route('register.verify.resend'), [
        'email' => 'TEST@example.com',
    ])->assertRedirect(route('register.verify.notice', ['email' => 'test@example.com']))
        ->assertSessionHas('status', 'Wysłaliśmy nowy kod aktywacyjny na podany adres e-mail.');

    $pending->refresh();

    expect($pending->verification_attempts)->toBe(0)
        ->and($pending->code_expires_at->greaterThan(now()->addMinutes(9)))->toBeTrue();

    Mail::assertSent(ActivationCodeMail::class, function (ActivationCodeMail $mail) use ($pending): bool {
        return $mail->hasTo('test@example.com')
            && Hash::check($mail->code, $pending->verification_code);
    });
});

test('new users can complete registration with activation code', function () {
    $pending = PendingRegistration::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => Hash::make('very-long-test-password'),
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
    $response->assertRedirect(route('home', absolute: false));
    expect(User::where('email', 'test@example.com')->exists())->toBeTrue();
    expect(PendingRegistration::where('email', 'test@example.com')->exists())->toBeFalse();
});

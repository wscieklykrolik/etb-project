<?php

use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

test('email verification screen can be rendered', function () {
    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->get('/verify-email');

    $response->assertStatus(200);
});

test('email can be verified', function () {
    $user = User::factory()->unverified()->create();

    Event::fake();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)]
    );

    $response = $this->actingAs($user)->get($verificationUrl);

    Event::assertDispatched(Verified::class);
    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
    $response->assertRedirect(route('home', absolute: false).'?verified=1');
});

test('email is not verified with invalid hash', function () {
    $user = User::factory()->unverified()->create();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1('wrong-email')]
    );

    $this->actingAs($user)->get($verificationUrl);

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

test('verification mail contains Polish text and a valid expiring link for the current address', function () {
    $user = User::factory()->unverified()->create();
    $message = (new VerifyEmailNotification)->toMail($user);
    $url = $message->viewData['url'];
    $text = view('emails.verify-email', $message->viewData)->render();

    expect($message->subject)->toBe('Potwierdź adres e-mail w ETB')
        ->and($text)->toContain('Potwierdź aktualny adres e-mail')
        ->and($text)->toContain('Link jest ważny przez 60 min.')
        ->and($text)->toContain($url)
        ->and($text)->not->toContain('All rights reserved')
        ->and(URL::hasValidSignature(Request::create($url)))->toBeTrue();

    $this->actingAs($user)->get($url)->assertRedirect(route('home', absolute: false).'?verified=1');
    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();

    $this->travel(61)->minutes();
    expect(URL::hasValidSignature(Request::create($url)))->toBeFalse();
});

test('unverified accounts cannot enter private panels or write club content', function () {
    $admin = User::factory()->unverified()->create(['role' => User::ROLE_ADMIN]);
    $this->actingAs($admin);

    foreach (['dashboard', 'profile.edit', 'admin.dashboard', 'admin.users.emails.export', 'news.create', 'players.create', 'matches.create'] as $route) {
        $this->get(route($route))->assertRedirect(route('verification.notice'));
    }

    $this->post(route('matches.store'), [])->assertRedirect(route('verification.notice'));
    $this->post(route('news.store'), [])->assertRedirect(route('verification.notice'));
    $this->patch(route('admin.shop-settings.mode'), [])->assertRedirect(route('verification.notice'));
    $this->assertDatabaseCount('news', 0);
    $this->assertDatabaseCount('matches', 0);
});

test('an old verification link cannot confirm a newly changed email address', function () {
    Notification::fake();
    $user = User::factory()->create();
    $oldUrl = (new VerifyEmailNotification)->toMail($user)->viewData['url'];

    $this->actingAs($user)->patch(route('profile.update'), [
        'name' => $user->name,
        'email' => 'zmieniony@example.com',
    ])->assertRedirect(route('verification.notice'));

    $this->get($oldUrl)->assertForbidden();
    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();

    $newUrl = (new VerifyEmailNotification)->toMail($user->fresh())->viewData['url'];
    $this->get($newUrl)->assertRedirect(route('home', absolute: false).'?verified=1');
    $this->get(route('profile.edit'))->assertOk();
});

test('an unverified account can retry delivery and gets a Polish message on mail failure', function () {
    Notification::shouldReceive('send')->once()->andThrow(new RuntimeException('SMTP niedostępne'));
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)->from(route('verification.notice'))->post(route('verification.send'))
        ->assertRedirect(route('verification.notice'))
        ->assertSessionHas('mail_error', 'Nie udało się wysłać wiadomości. Spróbuj ponownie za chwilę.');
    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

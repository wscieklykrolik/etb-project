<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    config(['accounts.admin_only' => true]);
});

test('administrator logs in without an active mailbox', function () {
    $admin = User::factory()->create(['email' => 'admin@etb.pl', 'role' => User::ROLE_ADMIN]);
    $this->post('/login', ['email' => $admin->email, 'password' => 'password'])->assertSessionHasNoErrors();
    $this->assertAuthenticatedAs($admin);
});

test('other accounts cannot log in even with the correct password', function (string $email, string $role, bool $verified) {
    User::factory()->create(['email' => $email, 'role' => $role, 'email_verified_at' => $verified ? now() : null]);
    $this->post('/login', ['email' => $email, 'password' => 'password'])->assertSessionHasErrors('email');
    $this->assertGuest();
})->with([
    ['fan@etb.pl', User::ROLE_FAN, true],
    ['other@etb.pl', User::ROLE_ADMIN, true],
    ['admin@etb.pl', User::ROLE_FAN, true],
    ['admin@etb.pl', User::ROLE_ADMIN, false],
]);

test('public account endpoints are blocked', function (string $method, string $path) {
    $this->{$method}($path)->assertNotFound();
})->with([
    ['get', '/register'], ['post', '/register'], ['get', '/register/verify'],
    ['post', '/register/verify'], ['post', '/register/verify/resend'],
    ['get', '/forgot-password'], ['post', '/forgot-password'],
    ['get', '/reset-password/test'], ['post', '/reset-password'],
]);

test('existing public sessions cannot access account pages', function () {
    $this->actingAs(User::factory()->create())->get('/profile')->assertForbidden();
});

test('administrator can be provisioned privately without changing old accounts', function () {
    $old = User::factory()->create();
    $this->artisan('etb:admin')
        ->expectsQuestion('Hasło administratora (15–128 znaków)', 'Prywatne-haslo-2026!')
        ->expectsQuestion('Powtórz hasło administratora', 'Prywatne-haslo-2026!')
        ->assertExitCode(0);
    $admin = User::where('email', 'admin@etb.pl')->firstOrFail();
    expect($admin->isAdmin())->toBeTrue()
        ->and($admin->hasVerifiedEmail())->toBeTrue()
        ->and(Hash::check('Prywatne-haslo-2026!', $admin->password))->toBeTrue();
    $this->assertDatabaseHas('users', ['id' => $old->id, 'email' => $old->email]);
});

test('existing non-admin alias cannot be promoted by the command', function () {
    $user = User::factory()->create(['email' => 'admin@etb.pl']);
    $this->artisan('etb:admin', ['--reset-password' => true])->assertExitCode(1);
    expect($user->fresh()->role)->toBe($user->role);
});

test('password reset requires an explicit option and revokes database sessions', function () {
    config(['session.driver' => 'database']);
    $admin = User::factory()->create(['email' => 'admin@etb.pl', 'role' => User::ROLE_ADMIN, 'remember_token' => 'old-token']);
    DB::table('sessions')->insert(['id' => 'old-session', 'user_id' => $admin->id,
        'payload' => '', 'last_activity' => time()]);
    $this->artisan('etb:admin')->assertExitCode(1);
    $this->artisan('etb:admin', ['--reset-password' => true])
        ->expectsQuestion('Hasło administratora (15–128 znaków)', 'Nowe-prywatne-haslo!')
        ->expectsQuestion('Powtórz hasło administratora', 'Nowe-prywatne-haslo!')
        ->assertExitCode(0);
    expect(Hash::check('Nowe-prywatne-haslo!', $admin->fresh()->password))->toBeTrue()
        ->and($admin->fresh()->remember_token)->toBeNull();
    $this->assertDatabaseMissing('sessions', ['id' => 'old-session']);
});

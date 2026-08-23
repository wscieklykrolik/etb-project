<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MatchController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicAcademyController;
use App\Http\Controllers\PublicClubController;
use App\Http\Controllers\PublicNewsController;
use App\Http\Controllers\PublicProductController;
use App\Http\Controllers\PublicScheduleController;
use App\Http\Controllers\PublicTeamController;
use App\Http\Controllers\ThreeXThreeTournamentController;
use App\Http\Controllers\ThreeXThreeTournamentTeamController;
use App\Http\Controllers\UserDataController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

require __DIR__.'/auth.php';
require __DIR__.'/admin.php';

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('players', PlayerController::class)->except(['index', 'show']);

    Route::resource('news', NewsController::class)->except(['index', 'show']);
    Route::get('/admin/news/{news}/preview', [NewsController::class, 'preview'])->name('admin.news.preview');
    Route::patch('/admin/news/{news}/publish', [NewsController::class, 'publish'])->name('admin.news.publish');

    Route::middleware('role:athlete')->group(function () {
        Route::get('/athlete/data', [UserDataController::class, 'athlete'])->name('athlete.data');
    });

    Route::middleware('role:fan')->group(function () {
        Route::get('/fan/data', [UserDataController::class, 'fan'])->name('fan.data');
    });

    Route::middleware('role:employee')->group(function () {
        Route::get('/employee/data', [UserDataController::class, 'employee'])->name('employee.data');
    });

    Route::middleware('role:trainer')->group(function () {
        Route::get('/trainer/data', [UserDataController::class, 'trainer'])->name('trainer.data');
    });
});

Route::resource('players', PlayerController::class)->only(['index', 'show']);

Route::get('/news', [PublicNewsController::class, 'index'])->name('news.index');
Route::get('/news/{news}', [PublicNewsController::class, 'show'])->name('news.show');

Route::resource('matches', MatchController::class);

Route::get('/shop', [PublicProductController::class, 'index'])->name('shop.index');
Route::get('/shop/{product}', [PublicProductController::class, 'show'])->name('shop.show');

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
Route::get('/cart/badge', [CartController::class, 'badge'])->name('cart.badge');

Route::middleware('auth')->group(function () {
    Route::get('/checkout/shipping', [CheckoutController::class, 'shipping'])->name('checkout.shipping');
    Route::post('/checkout/shipping', [CheckoutController::class, 'storeShipping']);
    Route::get('/checkout/payment', [CheckoutController::class, 'payment'])->name('checkout.payment');
    Route::post('/checkout/place', [CheckoutController::class, 'place'])->name('checkout.place');
    Route::get('/checkout/confirmation/{order}', [CheckoutController::class, 'confirmation'])->name('checkout.confirmation');
});

Route::post('/payment/przelewy24/webhook', [CheckoutController::class, 'webhook'])->name('payment.przelewy24.webhook');

/*
|--------------------------------------------------------------------------
| Strony główne menu
|--------------------------------------------------------------------------
*/
Route::get('/club', [PublicClubController::class, 'index'])->name('club');
Route::get('/schedule', [PublicScheduleController::class, 'index'])->name('schedule');
Route::get('/schedule/matches/{match}', [PublicScheduleController::class, 'show'])->name('schedule.matches.show');
Route::get('/team', [PublicTeamController::class, 'index'])->name('team');
Route::get('/contact', [PublicClubController::class, 'contact'])->name('contact');

/* Klub */
Route::get('/club/history', [PublicClubController::class, 'show'])->defaults('section', 'history')->name('club.history');
Route::get('/club/board', [PublicClubController::class, 'show'])->defaults('section', 'board')->name('club.board');
Route::get('/club/venue', [PublicClubController::class, 'show'])->defaults('section', 'venue')->name('club.venue');
Route::get('/club/business', [PublicClubController::class, 'show'])->defaults('section', 'business')->name('club.business');
Route::get('/club/success', [PublicClubController::class, 'show'])->defaults('section', 'success')->name('club.success');
Route::get('/club/sponsors', [PublicClubController::class, 'show'])->defaults('section', 'sponsors')->name('club.sponsors');
Route::get('/club/contact', [PublicClubController::class, 'contact'])->name('club.contact');

/* Rozgrywki */
Route::get('/schedule/lzkosz', [PublicScheduleController::class, 'lzkosz'])->name('schedule.lzkosz');
Route::redirect('/schedule/third-league', 'https://www.lzkosz.pl/liga/215.html')->name('schedule.third-league');
Route::get('/schedule/table', [PublicScheduleController::class, 'table'])->name('schedule.table');
Route::get('/schedule/3x3', [ThreeXThreeTournamentController::class, 'participating'])->name('schedule.3x3');
Route::redirect('/schedule/3x3-tournaments', '/schedule/3x3')->name('schedule.3x3.tournaments.old');
Route::redirect('/schedule/3x3/tournaments', '/schedule/3x3')->name('schedule.3x3.tournaments');
Route::redirect('/schedule/3x3/team', '/team/3x3')->name('schedule.3x3.team');

/* Drużyna */
Route::get('/team/players', [PublicTeamController::class, 'players'])->name('team.players');
Route::get('/team/players/{player}', [PublicTeamController::class, 'player'])->name('team.players.show');
Route::get('/team/staff', [PublicTeamController::class, 'staff'])->name('team.staff');
Route::get('/team/3x3', [PublicTeamController::class, 'threeXThree'])->name('team.3x3');
Route::redirect('/team-3x3/players', '/team/3x3')->name('team3x3.players');
Route::get('/3x3/tournaments', [ThreeXThreeTournamentController::class, 'index'])->name('three-x-three.tournaments.index');
Route::get('/3x3/tournaments/{tournament}', [ThreeXThreeTournamentController::class, 'show'])->name('three-x-three.tournaments.show');
Route::post('/3x3/tournaments/{tournament}/teams', [ThreeXThreeTournamentTeamController::class, 'store'])
    ->middleware('auth')
    ->name('three-x-three.tournaments.teams.store');
Route::get('/3x3/teams/{team}', [ThreeXThreeTournamentTeamController::class, 'show'])->name('three-x-three.teams.show');

/* CTA */
Route::view('/tickets', 'pages.tickets')->name('tickets');
Route::get('/academy', [PublicAcademyController::class, 'index'])->name('academy');
Route::get('/academy/groups/{group}', [PublicAcademyController::class, 'show'])->name('academy.groups.show');

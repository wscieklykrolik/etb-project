<?php

use App\Http\Controllers\Admin\UserRoleController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\PlayerController;
use App\Models\AppSetting;
use App\Models\Game;
use App\Models\News;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $latestNews = News::query()->with('author')->latest()->take(5)->get();

    return view('home', compact('latestNews'));
})->name('home');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

Route::resource('players', PlayerController::class)->only(['index', 'show']);

Route::middleware(['auth'])->group(function () {
    Route::resource('players', PlayerController::class)->except(['index', 'show']);
});


Route::get('/news', [NewsController::class, 'index'])->name('news.index');
Route::get('/news/{news}', [NewsController::class, 'show'])->whereNumber('news')->name('news.show');

Route::middleware(['auth'])->group(function () {
    Route::get('/news/create', [NewsController::class, 'create'])->name('news.create');
    Route::post('/news', [NewsController::class, 'store'])->name('news.store');
    Route::get('/news/{news}/edit', [NewsController::class, 'edit'])->whereNumber('news')->name('news.edit');
    Route::put('/news/{news}', [NewsController::class, 'update'])->whereNumber('news')->name('news.update');
    Route::delete('/news/{news}', [NewsController::class, 'destroy'])->whereNumber('news')->name('news.destroy');
});

/*
|--------------------------------------------------------------------------
| Strony główne menu
|--------------------------------------------------------------------------
*/
Route::view('/club', 'pages.club')->name('club');
Route::view('/schedule', 'pages.schedule')->name('schedule');
Route::view('/team', 'pages.team')->name('team');
Route::view('/contact', 'pages.contact')->name('contact');

/* Aktualności */
Route::view('/news/articles', 'pages.news-articles')->name('news.articles');
Route::view('/news/videos', 'pages.news-videos')->name('news.videos');
Route::view('/news/galleries', 'pages.news-galleries')->name('news.galleries');

/* Klub */
Route::view('/club/history', 'pages.club-history')->name('club.history');
Route::view('/club/board', 'pages.club-board')->name('club.board');
Route::view('/club/venue', 'pages.club-venue')->name('club.venue');
Route::view('/club/business', 'pages.club-business')->name('club.business');
Route::view('/club/investors', 'pages.club-investors')->name('club.investors');
Route::view('/club/success', 'pages.club-success')->name('club.success');
Route::view('/club/sponsors', 'pages.club-sponsors')->name('club.sponsors');

/* Rozgrywki */
Route::view('/schedule/lzkosz', 'pages.schedule-mzkosz')->name('schedule.lzkosz');
Route::redirect('/schedule/third-league', 'https://www.lzkosz.pl/liga/215.html')->name('schedule.third-league');
Route::view('/schedule/table', 'pages.schedule-table')->name('schedule.table');
Route::view('/schedule/3x3', 'pages.schedule-3x3')->name('schedule.3x3');
Route::view('/schedule/3x3/tournaments', 'pages.schedule-3x3-tournaments')->name('schedule.3x3.tournaments');
Route::view('/schedule/3x3/team', 'pages.schedule-3x3-team')->name('schedule.3x3.team');

/* Drużyna */
Route::view('/team/players', 'pages.team-players')->name('team.players');
Route::view('/team/staff', 'pages.team-staff')->name('team.staff');
Route::view('/team-3x3/players', 'pages.team-3x3-players')->name('team3x3.players');

/* CTA */
Route::view('/tickets', 'pages.tickets')->name('tickets');
Route::view('/shop', 'pages.shop')->name('shop');
Route::view('/academy', 'pages.academy')->name('academy');

Route::middleware(['auth', 'role:admin,employee', 'can:manage-matches'])->group(function () {
    Route::get('/admin/matches/create', function () {
        $defaultHomeLogo = AppSetting::getValue('default_home_logo');

        return view('admin.create-match', compact('defaultHomeLogo'));
    })->name('admin.matches.create');

    Route::post('/admin/matches', function (Request $request) {
        $validated = $request->validate([
            'opponent' => ['required', 'string', 'max:255'],
            'match_date' => ['required', 'date'],
            'location' => ['required', 'string', 'max:255'],
            'exact_address' => ['nullable', 'string', 'max:500'],
            'is_home' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'max:5120'],
            'away_logo' => ['nullable', 'image', 'max:5120'],
            'default_home_logo' => ['nullable', 'image', 'max:5120'],
        ]);

        $defaultHomeLogo = AppSetting::getValue('default_home_logo');

        if ($request->hasFile('default_home_logo')) {
            $defaultHomeLogo = $request->file('default_home_logo')->store('logos', 'public');
            AppSetting::setValue('default_home_logo', $defaultHomeLogo);
        }

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('matches', 'public');
        }

        if ($request->hasFile('away_logo')) {
            $validated['away_logo'] = $request->file('away_logo')->store('logos', 'public');
        }

        $validated['home_logo'] = $defaultHomeLogo;
        $validated['is_home'] = $request->boolean('is_home');

        Game::create($validated);

        return redirect()->route('admin.matches.create')->with('status', 'Mecz został zapisany.');
    })->name('admin.matches.store');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::patch('/admin/users/{user}/role', [UserRoleController::class, 'update'])->name('admin.users.role.update');
});

Route::middleware(['auth', 'role:athlete'])->group(function () {
    Route::get('/athlete/data', function () {
        return response()->json(request()->user()->athleteProfile);
    })->name('athlete.data');

    Route::get('/fan/data', function () {
        $user = request()->user();
        abort_if($user->role !== User::ROLE_FAN, 403);

        return response()->json($user->fanProfile);
    })->name('fan.data');

    Route::get('/employee/data', function () {
        $user = request()->user();
        abort_if($user->role !== User::ROLE_EMPLOYEE, 403);

        return response()->json($user->employeeProfile);
    })->name('employee.data');
});

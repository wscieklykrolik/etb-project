<?php

use App\Http\Controllers\Admin\UserRoleController;
use App\Http\Controllers\ProfileController;
use App\Models\Game;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $nextMatch = Game::where('match_date', '>=', now())
        ->orderBy('match_date')
        ->first();

    $upcomingGames = Game::where('match_date', '>=', now())
        ->orderBy('match_date')
        ->take(3)
        ->get();

    $latestArticles = collect([
        [
            'title' => 'ETB wygrywa po dogrywce',
            'category' => 'Artykuł',
            'date' => '22.04.2026',
            'excerpt' => 'Mocny finisz w ostatnich minutach i cenne zwycięstwo na własnym parkiecie.',
        ],
        [
            'title' => 'Kulisy meczu z ŁKS',
            'category' => 'Wideo',
            'date' => '21.04.2026',
            'excerpt' => 'Zobacz materiały zza kulis i reakcje zespołu po spotkaniu.',
        ],
        [
            'title' => 'Galeria zdjęć: derby Łodzi',
            'category' => 'Galeria',
            'date' => '20.04.2026',
            'excerpt' => 'Najlepsze kadry z gorącego spotkania i oprawy kibiców.',
        ],
        [
            'title' => 'Zapowiedź kolejki ŁZKosz',
            'category' => 'Artykuł',
            'date' => '19.04.2026',
            'excerpt' => 'Sprawdź analizę rywala i plan ETB na najbliższe spotkanie.',
        ],
        [
            'title' => 'Skrót meczu ETB - Start',
            'category' => 'Wideo',
            'date' => '18.04.2026',
            'excerpt' => 'Najciekawsze akcje i podsumowanie spotkania.',
        ],
        [
            'title' => 'Młodzież ETB na turnieju',
            'category' => 'Artykuł',
            'date' => '17.04.2026',
            'excerpt' => 'Relacja z występu młodych zawodników.',
        ],
        [
            'title' => 'Galeria: trening otwarty',
            'category' => 'Galeria',
            'date' => '16.04.2026',
            'excerpt' => 'Zdjęcia z otwartego treningu dla kibiców.',
        ],
        [
            'title' => 'Wywiad z trenerem',
            'category' => 'Wideo',
            'date' => '15.04.2026',
            'excerpt' => 'Plany zespołu na końcówkę sezonu.',
        ],
        [
            'title' => 'Transfer do ETB',
            'category' => 'Artykuł',
            'date' => '14.04.2026',
            'excerpt' => 'Nowy zawodnik dołącza do składu.',
        ],
        [
            'title' => 'Galeria: ETB vs AZS',
            'category' => 'Galeria',
            'date' => '13.04.2026',
            'excerpt' => 'Galeria meczowa z ostatniego pojedynku.',
        ],
        [
            'title' => 'Konferencja pomeczowa',
            'category' => 'Wideo',
            'date' => '12.04.2026',
            'excerpt' => 'Komentarze zawodników i sztabu.',
        ],
        [
            'title' => 'Współpraca z partnerem',
            'category' => 'Artykuł',
            'date' => '11.04.2026',
            'excerpt' => 'Nowe partnerstwo dla rozwoju klubu.',
        ],
        [
            'title' => 'Galeria: dzień meczowy',
            'category' => 'Galeria',
            'date' => '10.04.2026',
            'excerpt' => 'Kibice, emocje i atmosfera hali.',
        ],
        [
            'title' => 'Top akcje tygodnia',
            'category' => 'Wideo',
            'date' => '09.04.2026',
            'excerpt' => 'Najlepsze zagrania ostatnich spotkań.',
        ],
        [
            'title' => 'Raport medyczny',
            'category' => 'Artykuł',
            'date' => '08.04.2026',
            'excerpt' => 'Aktualizacja statusu zdrowotnego zawodników.',
        ],
        [
            'title' => 'Galeria: ETB Family Day',
            'category' => 'Galeria',
            'date' => '07.04.2026',
            'excerpt' => 'Zdjęcia z klubowego spotkania z fanami.',
        ],
    ]);

    $next3x3Tournament = [
        'name' => '3x3 Quest Łódź',
        'date' => '30.04.2026 16:00',
        'place' => 'Atlas Arena, Łódź',
    ];

    return view('pages.home', compact('nextMatch', 'upcomingGames', 'latestArticles', 'next3x3Tournament'));
})->name('home');

Route::get('/dashboard', function () {
    return redirect()->route('account');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/account', function () {
    $users = request()->user()->isAdmin()
        ? User::query()->select(['id', 'email', 'first_name', 'last_name', 'role'])->orderBy('email')->get()
        : collect();

    return view('dashboard', compact('users'));
})->middleware(['auth', 'verified'])->name('account');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| Strony główne menu
|--------------------------------------------------------------------------
*/
Route::view('/news', 'pages.news')->name('news');
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
Route::redirect('/schedule/mzkosz', '/schedule/lzkosz');
Route::redirect('/schedule/third-league', 'https://www.lzkosz.pl/')->name('schedule.third-league');
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


Route::middleware(['auth', 'role:admin,trainer'])->group(function () {
    Route::view('/academy/manage', 'pages.academy')->name('academy.manage');
});

Route::middleware(['auth', 'role:admin,employee'])->group(function () {
    Route::get('/admin/matches/create', function () {
        return view('admin.create-match');
    })->name('admin.matches.create');

    Route::post('/admin/matches', function (Request $request) {
        $data = $request->all();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('matches', 'public');
        }

        if ($request->hasFile('home_logo')) {
            $data['home_logo'] = $request->file('home_logo')->store('logos', 'public');
        }

        if ($request->hasFile('away_logo')) {
            $data['away_logo'] = $request->file('away_logo')->store('logos', 'public');
        }

        Game::create($data);

        return redirect('/')->with('status', 'Mecz został zapisany.');
    })->name('admin.matches.store');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::patch('/admin/users/{user}/role', [UserRoleController::class, 'update'])->name('admin.users.role.update');
});

Route::middleware('auth')->group(function () {
    Route::get('/athlete/data', function () {
        $user = request()->user();
        abort_if($user->role !== User::ROLE_ATHLETE, 403);

        return response()->json($user->athleteProfile);
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

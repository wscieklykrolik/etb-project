<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\UserRoleController;
use Illuminate\Support\Facades\Route;
use App\Models\Game;
use Illuminate\Http\Request;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| STRONA GŁÓWNA
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    $nextMatch = Game::where('match_date', '>=', now())
        ->orderBy('match_date')
        ->first();

    return view('pages.home', compact('nextMatch'));
})->name('home');

/*
|--------------------------------------------------------------------------
| DASHBOARD
|--------------------------------------------------------------------------
*/

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

/*
|--------------------------------------------------------------------------
| PROFILE
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/

require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| STRONY
|--------------------------------------------------------------------------
*/

Route::view('/team', 'pages.team')->name('team');
Route::view('/schedule', 'pages.schedule')->name('schedule');
Route::view('/news', 'pages.news')->name('news');
Route::view('/contact', 'pages.contact')->name('contact');
Route::view('/tickets', 'pages.tickets')->name('tickets');
Route::view('/shop', 'pages.shop')->name('shop');
Route::view('/academy', 'pages.academy')->name('academy');

/*
|--------------------------------------------------------------------------
| ADMIN
|--------------------------------------------------------------------------
*/

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

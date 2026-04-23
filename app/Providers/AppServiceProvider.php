<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('assign-roles', fn (User $user): bool => $user->isAdmin());
        Gate::define('access-admin-panel', fn (User $user): bool => $user->hasAnyRole([User::ROLE_ADMIN, User::ROLE_EMPLOYEE]));
    }
}

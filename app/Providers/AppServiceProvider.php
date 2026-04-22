<?php

namespace App\Providers;

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
        //
    }
}
use Illuminate\Support\Facades\Gate;

public function boot(): void
{
    Gate::define('isAdmin', function ($user) {
        return $user->is_admin;
    });
}

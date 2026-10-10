<?php

namespace App\Http\Middleware;

class Authenticate extends \Illuminate\Auth\Middleware\Authenticate
{
    protected function authenticate($request, array $guards)
    {
        parent::authenticate($request, $guards);

        if (config('accounts.admin_only') && ! $request->routeIs('logout')) {
            $user = $request->user();
            abort_unless($user->isAdmin()
                && $user->email === config('accounts.admin_login')
                && $user->hasVerifiedEmail(), 403);
        }
    }
}

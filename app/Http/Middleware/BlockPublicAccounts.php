<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class BlockPublicAccounts
{
    public function handle(Request $request, Closure $next)
    {
        abort_if(config('accounts.admin_only'), 404);

        return $next($request);
    }
}

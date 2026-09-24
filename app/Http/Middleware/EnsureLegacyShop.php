<?php

namespace App\Http\Middleware;

use App\Support\ShopSettings;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureLegacyShop
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! ShopSettings::legacy()) {
            if ($request->isMethod('GET')) {
                return redirect()->route('message-order.show');
            }

            abort(403, 'Płatności online i automatyczna wysyłka są obecnie wyłączone. Zamów przez wiadomość.');
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('home', absolute: false));
        }

        try {
            $request->user()->sendEmailVerificationNotification();
        } catch (Throwable $exception) {
            Log::warning('Nie udało się ponownie wysłać linku potwierdzającego adres e-mail.', [
                'exception' => $exception::class,
            ]);

            return back()->with('mail_error', 'Nie udało się wysłać wiadomości. Spróbuj ponownie za chwilę.');
        }

        return back()->with('status', 'verification-link-sent');
    }
}

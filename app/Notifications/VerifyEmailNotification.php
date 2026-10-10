<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailNotification extends VerifyEmail
{
    protected function buildMailMessage($url): MailMessage
    {
        return (new MailMessage)
            ->subject('Potwierdź adres e-mail w ETB')
            ->text('emails.verify-email', [
                'url' => $url,
                'expiresMinutes' => (int) config('auth.verification.expire', 60),
            ]);
    }
}

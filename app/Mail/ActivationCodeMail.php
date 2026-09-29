<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class ActivationCodeMail extends Mailable
{
    public function __construct(
        public string $code,
        public int $ttlMinutes,
    ) {}

    public function build()
    {
        return $this->subject('Kod aktywacyjny ETB')
            ->text('emails.activation-code', [
                'code' => $this->code,
                'ttlMinutes' => $this->ttlMinutes,
            ]);
    }
}

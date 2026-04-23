<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendingRegistration extends Model
{
    protected $fillable = [
        'name',
        'email',
        'password',
        'accepted_terms',
        'accepted_privacy',
        'verification_code',
        'code_expires_at',
    ];

    protected function casts(): array
    {
        return [
            'accepted_terms' => 'boolean',
            'accepted_privacy' => 'boolean',
            'code_expires_at' => 'datetime',
        ];
    }
}

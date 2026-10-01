<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchResultReminderDismissal extends Model
{
    protected $fillable = [
        'user_id',
        'match_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function match(): BelongsTo
    {
        return $this->belongsTo(TeamMatch::class, 'match_id');
    }
}

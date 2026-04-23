<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FanProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'can_buy_tickets',
        'can_buy_merch',
    ];

    protected function casts(): array
    {
        return [
            'can_buy_tickets' => 'boolean',
            'can_buy_merch' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

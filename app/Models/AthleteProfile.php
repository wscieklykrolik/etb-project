<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AthleteProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'performance_parameters',
        'playbook',
        'opponents_intel',
    ];

    protected function casts(): array
    {
        return [
            'performance_parameters' => 'array',
            'playbook' => 'array',
            'opponents_intel' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

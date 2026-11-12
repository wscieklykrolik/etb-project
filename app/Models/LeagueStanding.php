<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeagueStanding extends Model
{
    use HasFactory;

    protected $fillable = [
        'opponent_id',
        'league_id',
        'season',
        'position',
        'points',
        'games',
        'wins',
        'losses',
        'home_wins',
        'home_losses',
        'away_wins',
        'away_losses',
        'points_for',
        'points_against',
        'points_difference',
        'ratio',
        'last_five',
        'source_team_name',
        'source_team_url',
        'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'synced_at' => 'datetime',
            'ratio' => 'decimal:4',
            'last_five' => 'array',
        ];
    }

    public function opponent(): BelongsTo
    {
        return $this->belongsTo(Opponent::class);
    }
}

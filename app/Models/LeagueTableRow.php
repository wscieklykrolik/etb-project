<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeagueTableRow extends Model
{
    protected $fillable = [
        'position', 'team_name', 'logo_path', 'points', 'games', 'wins', 'losses',
        'home_wins', 'home_losses', 'away_wins', 'away_losses', 'points_for',
        'points_against', 'points_difference', 'ratio', 'sort_order',
    ];

    protected function casts(): array
    {
        return ['ratio' => 'decimal:4'];
    }

    public function snapshot(): BelongsTo
    {
        return $this->belongsTo(LeagueTableSnapshot::class, 'league_table_snapshot_id');
    }
}

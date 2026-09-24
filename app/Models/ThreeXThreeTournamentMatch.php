<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThreeXThreeTournamentMatch extends Model
{
    use HasFactory;

    public const STAGE_GROUP = 'group';

    public const STAGE_PLAYOFF = 'playoff';

    protected $fillable = [
        'three_x_three_tournament_id',
        'group_id',
        'team_one_id',
        'team_two_id',
        'team_one_placeholder',
        'team_two_placeholder',
        'stage',
        'round_label',
        'bracket_round_order',
        'bracket_position',
        'team_one_score',
        'team_two_score',
        'played_at',
        'court',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'played_at' => 'datetime',
            'team_one_score' => 'integer',
            'team_two_score' => 'integer',
            'bracket_round_order' => 'integer',
            'bracket_position' => 'integer',
        ];
    }

    public function hasResult(): bool
    {
        return $this->team_one_score !== null && $this->team_two_score !== null;
    }

    public function winnerId(): ?int
    {
        if (! $this->hasResult() || $this->team_one_score === $this->team_two_score) {
            return null;
        }

        return $this->team_one_score > $this->team_two_score ? $this->team_one_id : $this->team_two_id;
    }

    public function loserId(): ?int
    {
        if (! $this->hasResult() || $this->team_one_score === $this->team_two_score) {
            return null;
        }

        return $this->team_one_score > $this->team_two_score ? $this->team_two_id : $this->team_one_id;
    }

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(ThreeXThreeTournament::class, 'three_x_three_tournament_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(ThreeXThreeTournamentGroup::class, 'group_id');
    }

    public function teamOne(): BelongsTo
    {
        return $this->belongsTo(ThreeXThreeTournamentTeam::class, 'team_one_id');
    }

    public function teamTwo(): BelongsTo
    {
        return $this->belongsTo(ThreeXThreeTournamentTeam::class, 'team_two_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamMatch extends Model
{
    use HasFactory;

    protected $table = 'matches';

    public const STATUS_UPCOMING = 'upcoming';

    public const STATUS_FINISHED = 'finished';

    public const LZKOSZ_ROUND_ONE = 'round_1';

    public const LZKOSZ_ROUND_TWO = 'round_2';

    protected $fillable = [
        'opponent_name',
        'opponent_id',
        'match_date',
        'location',
        'sports_hall_id',
        'is_home',
        'our_score',
        'opponent_score',
        'opponent_logo',
        'home_logo',
        'image',
        'stream_link',
        'status',
        'season',
        'include_in_lzkosz',
        'lzkosz_round',
        'publish_at',
        'notes',
        'is_ticketed',
        'ticket_url',
    ];

    protected function casts(): array
    {
        return [
            'match_date' => 'datetime',
            'is_home' => 'boolean',
            'include_in_lzkosz' => 'boolean',
            'our_score' => 'integer',
            'opponent_score' => 'integer',
            'publish_at' => 'datetime',
            'is_ticketed' => 'boolean',
        ];
    }

    public function hasResult(): bool
    {
        return $this->our_score !== null && $this->opponent_score !== null;
    }

    public function effectiveStatus(): string
    {
        if ($this->status === self::STATUS_FINISHED) {
            return self::STATUS_FINISHED;
        }

        if ($this->match_date?->lte(now()->subHours(2))) {
            return self::STATUS_FINISHED;
        }

        return self::STATUS_UPCOMING;
    }

    public function isFinished(): bool
    {
        return $this->effectiveStatus() === self::STATUS_FINISHED;
    }

    public function isUpcoming(): bool
    {
        return ! $this->isFinished();
    }

    public function statusLabel(): string
    {
        return $this->isFinished() ? 'Zakończony' : 'Nadchodzący';
    }

    public function resultLabel(): string
    {
        if (! $this->hasResult()) {
            return $this->isFinished() ? 'Wynik nieuzupełniony' : 'Do rozegrania';
        }

        return "{$this->our_score}:{$this->opponent_score}";
    }

    public function isPublished(): bool
    {
        return $this->publish_at === null || $this->publish_at->isPast();
    }

    public function isWin(): ?bool
    {
        if (! $this->hasResult()) {
            return null;
        }

        return $this->our_score > $this->opponent_score;
    }

    public function ticketSalesActive(): bool
    {
        return $this->is_ticketed && filled($this->ticket_url);
    }

    public function lzkoszRoundLabel(): string
    {
        return match ($this->lzkosz_round) {
            self::LZKOSZ_ROUND_TWO => 'Runda 2',
            default => 'Runda 1',
        };
    }

    public function opponent(): BelongsTo
    {
        return $this->belongsTo(Opponent::class);
    }

    public function sportsHall(): BelongsTo
    {
        return $this->belongsTo(SportsHall::class);
    }
}

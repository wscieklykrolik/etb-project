<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ThreeXThreeTournament extends Model
{
    use HasFactory;

    public const STATUS_UPCOMING = 'upcoming';

    public const STATUS_FINISHED = 'finished';

    public const TYPE_PARTICIPATING = 'participating';

    public const TYPE_ORGANIZED = 'organized';

    public const REGISTRATION_NONE = 'none';

    public const REGISTRATION_EXTERNAL = 'external';

    public const REGISTRATION_INTERNAL = 'internal';

    protected $fillable = [
        'name',
        'date',
        'location',
        'description',
        'status',
        'organizer',
        'image_path',
        'type',
        'registration_mode',
        'registration_url',
        'registration_enabled',
        'team_size',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'registration_enabled' => 'boolean',
            'team_size' => 'integer',
        ];
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_UPCOMING);
    }

    public function scopeFinished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_FINISHED);
    }

    public function scopeParticipating(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_PARTICIPATING);
    }

    public function scopeOrganized(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_ORGANIZED);
    }

    public function acceptsInternalRegistrations(): bool
    {
        return $this->type === self::TYPE_ORGANIZED
            && $this->registration_mode === self::REGISTRATION_INTERNAL
            && $this->registration_enabled
            && $this->status === self::STATUS_UPCOMING;
    }

    public function categories(): HasMany
    {
        return $this->hasMany(ThreeXThreeTournamentCategory::class);
    }

    public function teams(): HasMany
    {
        return $this->hasMany(ThreeXThreeTournamentTeam::class);
    }

    public function groups(): HasMany
    {
        return $this->hasMany(ThreeXThreeTournamentGroup::class);
    }

    public function matches(): HasMany
    {
        return $this->hasMany(ThreeXThreeTournamentMatch::class);
    }
}

<?php

namespace App\Models;

use App\Enums\ThreeXThreeCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ThreeXThreeTournamentTeam extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_ACCEPTED = 'accepted';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'three_x_three_tournament_id',
        'user_id',
        'name',
        'category',
        'logo_path',
        'status',
        'group_id',
    ];

    protected function casts(): array
    {
        return [
            'category' => ThreeXThreeCategory::class,
        ];
    }

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(ThreeXThreeTournament::class, 'three_x_three_tournament_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(ThreeXThreeTournamentGroup::class, 'group_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function players(): HasMany
    {
        return $this->hasMany(ThreeXThreeTournamentTeamPlayer::class);
    }
}

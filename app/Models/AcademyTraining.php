<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcademyTraining extends Model
{
    use HasFactory;

    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'academy_group_id',
        'recurrence_series_id',
        'title',
        'starts_at',
        'ends_at',
        'location',
        'trainer_name',
        'description',
        'status',
        'cancelled_reason',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(AcademyGroup::class, 'academy_group_id');
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function statusLabel(): string
    {
        return $this->isCancelled() ? 'Odwołany' : 'Planowany';
    }

    public function timeRange(): string
    {
        $start = $this->starts_at?->format('H:i');
        $end = $this->ends_at?->format('H:i');

        return $end ? "{$start}-{$end}" : (string) $start;
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('starts_at', '>=', now())->orderBy('starts_at');
    }
}

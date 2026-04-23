<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'can_manage_articles',
        'can_write_database',
        'can_read_database',
    ];

    protected function casts(): array
    {
        return [
            'can_manage_articles' => 'boolean',
            'can_write_database' => 'boolean',
            'can_read_database' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}


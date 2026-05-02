<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MatchGame extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'opponent',
        'match_date',
        'location',
        'result',
        'publish_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'match_date' => 'datetime',
            'publish_at' => 'datetime',
        ];
    }
}

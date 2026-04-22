<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    protected $fillable = [
        'opponent',
        'match_date',
        'location',
        'is_home',
        'image',
        'home_logo',
        'away_logo',
        'stream_link',
    ];
}

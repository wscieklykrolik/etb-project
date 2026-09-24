<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThreeXThreeMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'birth_year',
        'name',
        'role',
        'description',
        'photo_path',
        'is_coach',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'birth_year' => 'integer',
            'is_coach' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}

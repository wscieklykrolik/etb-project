<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamStaff extends Model
{
    use HasFactory;

    protected $table = 'team_staff';

    protected $fillable = [
        'birth_year',
        'name',
        'role',
        'description',
        'photo_path',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'birth_year' => 'integer',
            'sort_order' => 'integer',
        ];
    }
}

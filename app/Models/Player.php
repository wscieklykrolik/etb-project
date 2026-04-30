<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'number',
        'position',
        'date_of_birth',
        'height',
        'weight',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'number' => 'integer',
            'height' => 'integer',
            'weight' => 'integer',
        ];
    }

    public function getAgeAttribute(): ?int
    {
        $dateOfBirth = $this->date_of_birth;

        if (! $dateOfBirth instanceof CarbonInterface) {
            return null;
        }

        return $dateOfBirth->age;
    }
}

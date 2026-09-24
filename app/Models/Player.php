<?php

namespace App\Models;

use App\Enums\BasketballPosition;
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
        'birth_year',
        'first_name',
        'last_name',
        'number',
        'position',
        'date_of_birth',
        'height',
        'weight',
        'photo_path',
        'description',
        'publish_description',
        'is_starting_five',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'birth_year' => 'integer',
            'date_of_birth' => 'date',
            'number' => 'integer',
            'height' => 'integer',
            'weight' => 'integer',
            'publish_description' => 'boolean',
            'is_starting_five' => 'boolean',
        ];
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function positionLabel(): string
    {
        return BasketballPosition::tryFrom((string) $this->position)?->label() ?? (string) $this->position;
    }

    public function positionOrder(): int
    {
        return BasketballPosition::tryFrom((string) $this->position)?->sortOrder() ?? 99;
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

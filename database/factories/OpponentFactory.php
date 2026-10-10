<?php

namespace Database\Factories;

use App\Models\Opponent;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OpponentFactory extends Factory
{
    protected $model = Opponent::class;

    public function definition(): array
    {
        return [
            'name' => Str::limit($this->faker->company(), 180, '').' '.Str::uuid(),
        ];
    }
}

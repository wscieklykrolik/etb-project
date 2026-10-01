<?php

namespace Database\Factories;

use App\Models\Opponent;
use App\Models\SportsHall;
use App\Models\TeamMatch;
use Illuminate\Database\Eloquent\Factories\Factory;

class TeamMatchFactory extends Factory
{
    protected $model = TeamMatch::class;

    public function definition(): array
    {
        $opponent = Opponent::factory()->create();
        $sportsHall = SportsHall::factory()->create();

        return [
            'opponent_name' => $this->faker->company(),
            'opponent_id' => $opponent->id,
            'match_date' => $this->faker->dateTimeBetween('-1 month', '+2 months'),
            'has_time' => true,
            'location' => $sportsHall->name,
            'sports_hall_id' => $sportsHall->id,
            'is_home' => $this->faker->boolean(),
            'status' => $this->faker->randomElement([TeamMatch::STATUS_UPCOMING, TeamMatch::STATUS_FINISHED]),
            'our_score' => fn (array $attrs) => $attrs['status'] === TeamMatch::STATUS_FINISHED ? $this->faker->numberBetween(50, 120) : null,
            'opponent_score' => fn (array $attrs) => $attrs['status'] === TeamMatch::STATUS_FINISHED ? $this->faker->numberBetween(50, 120) : null,
        ];
    }
}

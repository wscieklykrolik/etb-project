<?php

use App\Models\Opponent;

test('opponent factory stays unique when faker repeats the same company', function () {
    fake()->seed(42);
    $first = Opponent::factory()->create();
    fake()->seed(42);
    $second = Opponent::factory()->create();
    fake()->seed();

    expect(substr($first->name, 0, -37))->toBe(substr($second->name, 0, -37))
        ->and($first->name)->not->toBe($second->name)
        ->and(Opponent::count())->toBe(2);
});

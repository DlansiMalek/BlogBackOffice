<?php

namespace Database\Factories;

use App\Models\AccessGame;
use Faker\Generator as Faker;

$factory->define(AccessGame::class, function (Faker $faker) {
    return [
        'score' => $this->faker->numberBetween(10, 100)
    ];
});

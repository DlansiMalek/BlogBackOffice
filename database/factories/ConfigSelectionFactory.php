<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\ConfigSelection;
use Faker\Generator as Faker;

$factory->define(ConfigSelection::class, function (Faker $faker) {
    return [
        'num_evaluators' => $faker->numberBetween(1, 10),
        'selection_type' => $faker->numberBetween(0, 2),
        'start_date' => $faker->date(),
        'end_date' => $faker->date()
    ];
});

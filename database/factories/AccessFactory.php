<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Access;
use Faker\Generator as Faker;

$factory->define(Access::class, function (Faker $faker) {
    return [
        'name' => $faker->sentence,
        'price' => $faker->randomFloat($nbMaxDecimals = NULL, $min = 0, $max = NULL),
        'duration' => $faker->numberBetween($min = 1, $max = 100),
        'max_places' => $faker->numberBetween($min = 1, $max = 100),
        'room' => $faker->sentence,
        'description' => $faker->sentence,
        'start_date' => $faker->date(),
        'end_date' => $faker->date(),
        'show_in_program' => $faker->numberBetween($min = 1, $max = 3),
        'show_in_register' => $faker->numberBetween($min = 1, $max = 3),
        'congress_id' => $faker->numberBetween($min = 1, $max = 3),
        'topic_id' => $faker->numberBetween($min = 1, $max = 2),
        'access_type_id' => $faker->numberBetween($min = 1, $max = 3),
    ];
});

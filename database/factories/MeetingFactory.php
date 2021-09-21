<?php

namespace Database\Factories;

use App\Models\Meeting;
use Faker\Generator as Faker;

$factory->define(Meeting::class, function (Faker $faker) {
    return [
        'name' => $faker->word,
        'start_date' => $faker->dateTime(),
        'end_date' => $faker->dateTime(),
    ];
});

<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Congress;
use Faker\Generator as Faker;

$factory->define(Congress::class, function (Faker $faker) {
    return [
        'name' => $faker->sentence,
        'start_date' => $faker->dateTime(),
        'end_date' => $faker->dateTime(),
        'price' => $faker->randomFloat(2, 0, 5000),
        'congress_type_id' => $faker->numberBetween(1, 3),
        'description' => $faker->paragraph
    ];
});

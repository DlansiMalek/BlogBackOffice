<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

namespace Database\Factories;
use App\Models\Congress;
use Faker\Generator as Faker;

$factory->define(Congress::class, function (Faker $faker) {
    $congressTypeId = $faker->numberBetween(1, 3);
    return [
        'name' => $faker->sentence,
        'start_date' => $faker->dateTime(),
        'end_date' => $faker->dateTime(),
        'price' => $congressTypeId == 1 ? $faker->randomFloat(2, 0, 5000) : 0,
        'congress_type_id' => $congressTypeId,
        'description' => $faker->paragraph
    ];
});

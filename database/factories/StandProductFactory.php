<?php

namespace Database\Factories;

use App\Models\StandProduct;
use Faker\Generator as Faker;

$factory->define(StandProduct::class, function (Faker $faker) {
    return [
        'name' => $faker->word,
        'description' => $faker->sentence,
        'main_img' => $faker->sentence
    ];
});


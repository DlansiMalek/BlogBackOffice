<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Pack;
use Faker\Generator as Faker;

$factory->define(Pack::class, function (Faker $faker) {
    return [
        'label' => $faker->sentence,
        'price' => $faker->randomFloat($nbMaxDecimals = NULL, $min = 0, $max = NULL),
        'description' => $faker->sentence,
        'congress_id' => 1
    ];
});

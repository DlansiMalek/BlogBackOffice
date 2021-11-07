<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

namespace Database\Factories;
use App\Models\GSTag;
use Faker\Generator as Faker;

$factory->define(GSTag::class, function (Faker $faker) {
    return [
        'label' => $faker->word,
    ];
});
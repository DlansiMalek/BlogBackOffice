<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

namespace Database\Factories;
use App\Models\UserAccess;
use Faker\Generator as Faker;

$factory->define(UserAccess::class, function (Faker $faker) {
    return [
        'isPresent' => $faker->numberBetween(0, 1)
    ];
});

<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Resource;
use Faker\Generator as Faker;

$factory->define(Resource::class, function (Faker $faker) {
    return [
        'path' => $faker->sentence,
        'size' => $faker->numberBetween(3000, 300000)
    ];
});

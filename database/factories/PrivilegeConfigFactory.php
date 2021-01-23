<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

namespace Database\Factories;
use App\Models\PrivilegeConfig;
use Faker\Generator as Faker;

$factory->define(PrivilegeConfig::class, function (Faker $faker) {
    return [
        'status' => $faker->numberBetween(1,2)
    ];
});

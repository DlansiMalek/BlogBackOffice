<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

namespace Database\Factories;
use App\Models\UserCongress;
use Faker\Generator as Faker;

$factory->define(UserCongress::class, function (Faker $faker) {
    return [
        /*'isPresent' => $faker->numberBetween($min = 0, $max = 1),
        'isSelected' => $faker->numberBetween($min = 0, $max = 1),
        'organization_accepted' => $faker->numberBetween($min = 0, $max = 1),*/
    ];
});

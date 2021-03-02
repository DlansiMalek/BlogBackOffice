<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

namespace Database\Factories;
use App\Models\Payment;
use Faker\Generator as Faker;

$factory->define(Payment::class, function (Faker $faker) {
    return [
        'isPaid' => $faker->numberBetween(-1,1),
        'path' => $faker->sentence,
        'free' => $faker->numberBetween(0,1),
        'price' => $faker->numberBetween(100, 1000)
    ];
});

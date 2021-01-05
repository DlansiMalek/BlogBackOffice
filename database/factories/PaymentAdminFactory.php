<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\PaymentAdmin;
use Faker\Generator as Faker;

$factory->define(PaymentAdmin::class, function (Faker $faker) {
    return [
        'isPaid' => $faker->numberBetween(0,1),
        'price' => $faker->numberBetween(500,1000)
    ];
});

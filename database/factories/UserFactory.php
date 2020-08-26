<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\User;
use Faker\Generator as Faker;

$factory->define(User::class, function (Faker $faker) {
    return [
        'first_name' => $faker->sentence,
        'last_name' => $faker->sentence,
        'gender' => 1,
        'mobile' => $faker->phoneNumber ,
        'email' => $faker->email,
        'email_verified' => 1,
        'qr_code' => $faker->sentence,
    ];
});

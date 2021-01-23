<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

namespace Database\Factories;
use App\Models\User;
use Faker\Generator as Faker;

$factory->define(User::class, function (Faker $faker) {
    $password = $faker->password;
    return [
        'first_name' => $faker->sentence,
        'last_name' => $faker->sentence,
        'gender' => 1,
        'mobile' => $faker->phoneNumber ,
        'email' => $faker->email,
        'email_verified' => 1,
        'qr_code' => $faker->sentence,
        'password' => bcrypt($password),
        'passwordDecrypt' => $password,
        'country_id' => 'USA',
        'verification_code' => $faker->sentence,

    ];
});

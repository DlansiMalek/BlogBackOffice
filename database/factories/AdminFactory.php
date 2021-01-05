<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Admin;
use Faker\Generator as Faker;

$factory->define(Admin::class, function (Faker $faker) {
    $password = $faker->password;
    return [
        'email' => $faker->email,
        'mobile' => $faker->phoneNumber,
        'name' => $faker->firstName,
        'privilege_id' => $faker->numberBetween(1, 3),
        'password' => bcrypt($password),
        'passwordDecrypt' => $password
    ];
});

<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\WhiteList;
use Faker\Generator as Faker;

$factory->define(WhiteList::class, function (Faker $faker) {
    return [
        'first_name' => $faker->firstName,
        'last_name' => $faker->lastName,
        'mobile' => $faker->phoneNumber ,
        'email' => $faker->email,
    ];
});

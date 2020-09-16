<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Author;
use Faker\Generator as Faker;

$factory->define(Author::class, function (Faker $faker) {
    return [
        'first_name' => $faker->firstName,
        'last_name' => $faker->lastName,
        'email' => $faker->email,
        'service_id' => $faker->numberBetween(127,151),
        'etablissement_id' => $faker->numberBetween(60,84),
        'rank' => $faker->numberBetween(1,3)
    ];
});

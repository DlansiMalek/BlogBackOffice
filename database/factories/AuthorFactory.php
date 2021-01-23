<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

namespace Database\Factories;
use App\Models\Author;
use Faker\Generator as Faker;

$factory->define(Author::class, function (Faker $faker) {
    return [
        'first_name' => $faker->firstName,
        'last_name' => $faker->lastName,
        'email' => $faker->email,
        'service_id' => $faker->numberBetween(1,127),
        'etablissement_id' => $faker->numberBetween(1,60),
        'rank' => $faker->numberBetween(1,3)
    ];
});

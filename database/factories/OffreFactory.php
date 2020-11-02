<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Offre;
use Faker\Generator as Faker;

$factory->define(Offre::class, function (Faker $faker) {
    return [
        'nom' => $faker->word,
        'value' => $faker->numberBetween(500,1000),
        'start_date' => $faker->date(),
        'end_date' => $faker->date(),
        'type_id' =>$faker->numberBetween(1,4)
    ];
});

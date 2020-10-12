<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Submission;
use Faker\Generator as Faker;

$factory->define(Submission::class, function (Faker $faker) {
    return [
        'title' => $faker->sentence,
        'description' => $faker->sentence,
        'code' => $faker->sentence,
        'communication_type_id' => $faker->numberBetween(1,2)
    ];
});

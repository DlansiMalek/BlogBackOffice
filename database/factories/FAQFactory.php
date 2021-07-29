<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\FAQ;
use Faker\Generator as Faker;

$factory->define(FAQ::class, function (Faker $faker) {
    return [
        'question' => $faker->word,
        'response' => $faker->word,
    ];
});

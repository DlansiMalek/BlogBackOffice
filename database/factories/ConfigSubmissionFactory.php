<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

namespace Database\Factories;
use App\Models\ConfigSubmission;
use App\Services\Utils;
use Faker\Generator as Faker;

$factory->define(ConfigSubmission::class, function (Faker $faker) {
    return [
        'end_submission_date' => $faker->date,
        'start_submission_date' => $faker->date,
        'max_words' => $faker->numberBetween(100, 500),
        'num_evaluators' => $faker->numberBetween(1, 5),
    ];
});

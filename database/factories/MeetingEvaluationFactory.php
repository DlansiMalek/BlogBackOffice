<?php

namespace Database\Factories;

use App\Models\MeetingEvaluation;
use Faker\Generator as Faker;

$factory->define(MeetingEvaluation::class, function (Faker $faker) {
    return [
        'comment' => $faker->word,
        'note' => $faker->numberBetween(0,5)
    ];
});

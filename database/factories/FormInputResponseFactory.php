<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

namespace Database\Factories;
use App\Models\FormInputResponse;
use Faker\Generator as Faker;

$factory->define(FormInputResponse::class, function (Faker $faker) {
    return [
        'response' => $faker->sentence
    ];
});

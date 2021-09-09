<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

namespace Database\Factories;
use App\Models\FormInputValue;
use Faker\Generator as Faker;

$factory->define(FormInputValue::class, function (Faker $faker) {
    return [
        'value' => $faker->word
    ];
});

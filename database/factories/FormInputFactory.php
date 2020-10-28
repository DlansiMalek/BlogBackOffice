<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\FormInput;
use Faker\Generator as Faker;

$factory->define(FormInput::class, function (Faker $faker) {
    return [
        'label'=> $faker->sentence,
        'required' => $faker->numberBetween(0, 1),
        'form_input_type_id' => $faker->numberBetween(1, 10)
    ];
});

<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

namespace Database\Factories;
use App\Models\STag;
use Faker\Generator as Faker;

$factory->define(STag::class, function (Faker $faker) {
    return [
        'label' => $faker->word,
    ];
});
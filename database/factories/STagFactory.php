<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

namespace Database\Factories;
use App\Models\STag;
use Faker\Generator as Faker;

$factory->define(STag::class, function (Faker $faker) {
    $gstag = $faker->numberBetween(1,20);
    return [
        'label' => $faker->word
    ];
});
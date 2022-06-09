<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

namespace Database\Factories;
use App\Models\RequestLandingPage;
use Faker\Generator as Faker;

$factory->define(RequestLandingPage::class, function (Faker $faker) {
    return [
        'dns' => $faker->word,
        'status' => 0,
    ];
});

<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Stand;
use Faker\Generator as Faker;

$factory->define(Stand::class, function (Faker $faker) {
    return [
        'name' => $faker->word,
        'status' => $faker->numberBetween(0,1),
        'url_streaming' => $faker->url,
    ];
});

<?php

namespace Database\Factories;

use App\Models\StandContentFile;
use Faker\Generator as Faker;

$factory->define(StandContentFile::class, function (Faker $faker) {
    $isFile = $faker->boolean();
    return [
        'file' => $isFile ? $faker->sentence : null,
        'url' => $isFile ? null : $faker->url
    ];
});


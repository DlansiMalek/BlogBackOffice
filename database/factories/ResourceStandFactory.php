<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\ResourceStand;
use Faker\Generator as Faker;

$factory->define(ResourceStand::class, function (Faker $faker) {
    return [
        'file_name' => $faker->word
    ];
});

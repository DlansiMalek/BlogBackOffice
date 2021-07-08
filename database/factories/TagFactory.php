<?php

namespace Database\Factories;

use App\Models\Tag;
use Faker\Generator as Faker;

$factory->define(Tag::class, function (Faker $faker) {
    return [
        'label' => $faker->word
    ];
});

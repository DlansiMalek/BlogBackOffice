<?php

namespace Database\Factories;

use App\Models\MeetingTable;
use Faker\Generator as Faker;

$factory->define(MeetingTable::class, function (Faker $faker) {
    return [
        'label' => $faker->word,
    ];
});

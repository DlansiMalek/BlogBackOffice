<?php

namespace Database\Factories;

use App\Models\UserMeeting;
use Faker\Generator as Faker;

$factory->define(UserMeeting::class, function (Faker $faker) {
    return [
        'status' => 0,
    ];
});

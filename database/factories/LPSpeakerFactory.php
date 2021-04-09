<?php

namespace Database\Factories;

use App\Models\LPSpeaker;
use Faker\Generator as Faker;
use App\Services\Utils;

$factory->define(LPSpeaker::class, function (Faker $faker) {
    return [
        'first_name' => $faker->firstName,
        'last_name' => $faker->lastName,
        'role' => $faker->word,
        'profile_img' => Utils::generateCode(0, 15) . ".png",
        'fb_link' => $faker->url,
        'linkedin_link' => $faker->url,
        'instagram_link' => $faker->url,
        'twitter_link' => $faker->url,
    ];
});

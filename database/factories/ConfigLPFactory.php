<?php

namespace Database\Factories;

use App\Models\ConfigLP;
use Faker\Generator as Faker;
use App\Services\Utils;

$factory->define(ConfigLP::class, function (Faker $faker) {
    return [
        'header_logo_event' => Utils::generateCode(0, 15) . ".png",
        'is_inscription' => $faker->numberBetween(0,1),
        'home_banner_event' => Utils::generateCode(0, 15) . ".png",
        'home_start_date' => $faker->dateTime(),
        'home_end_date' => $faker->dateTime(),
        'home_title' => $faker->sentence,
        'home_description' => $faker->paragraph,
        'prp_banner_event' => Utils::generateCode(0, 15) . ".png",
        'prp_title' => $faker->sentence,
        'prp_description' => $faker->paragraph,
        'speaker_title' => $faker->sentence,
        'speaker_description' => $faker->paragraph,
        'sponsor_title' => $faker->sentence,
        'sponsor_description' => $faker->paragraph,
        'prg_title' => $faker->sentence,
        'prg_description' => $faker->paragraph,
        'contact_title' => $faker->sentence,
        'contact_description' => $faker->paragraph,
        'event_link_fb' => $faker->url,
        'event_link_instagram' => $faker->url,
        'event_link_linkedin' => $faker->url,
        'event_link_twitter' => $faker->url
    ];
});

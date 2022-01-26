<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

namespace Database\Factories;
use App\Models\ConfigCongress;
use App\Services\Utils;
use Faker\Generator as Faker;

$factory->define(ConfigCongress::class, function (Faker $faker) {
    return [
        'logo' => 'congress-logo/' . Utils::generateCode(0, 15) . ".png",
        'banner' => 'congress-banner/' . Utils::generateCode(0, 15) . ".png",
        'free' => $faker->numberBetween(0, 100),
        'nb_meeting_table' => $this->faker->numberBetween(0, 1),
    ];
});

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
        'program_link' => 'https://eventizer.io',
        'has_payment' => $faker->numberBetween(0, 1),
        'is_online' => $faker->numberBetween(0, 1),
        'is_code_shown' => $faker->numberBetween(0, 1),
        'is_notif_register_mail' => $faker->numberBetween(0, 1),
        'register_disabled' => $faker->numberBetween(0, 1),
        'is_notif_sms_confirm' => $faker->numberBetween(0, 1),
        'is_submission_enabled' => $faker->numberBetween(0, 1),
        'application' => $faker->numberBetween(0, 1),
        'nb_current_participants' => $faker->numberBetween(0, 1),
        'max_online_participants' => $faker->numberBetween(0, 1),
        'is_upload_user_img' => $faker->numberBetween(0, 1),
        'is_sponsor_logo' => $faker->numberBetween(0, 1),
        'mobile_technical' => $faker->phoneNumber
    ];
});

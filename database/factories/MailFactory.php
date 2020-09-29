<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Mail;
use Faker\Generator as Faker;

$factory->define(Mail::class, function (Faker $faker) {
    return [
        'object' => "Test object",
        'template' => $faker->randomHtml(2,1) ,
        'congress_id' => 1,
        'mail_type_id' =>$faker->numberBetween(1, 20),
    ];
});

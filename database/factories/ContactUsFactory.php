<?php
/** @var \Illuminate\Database\Eloquent\Factory $factory */
use App\Models\ContactUs;
use Faker\Generator as Faker;

$factory->define(ContactUs::class, function (Faker $faker) {
    return [
        'email' => $faker->word,
        'user_name' => $faker->word,
        'subject'   => $faker->word,
        'message'  => $faker->word,
        'mobile' => $faker->word,
    ];
});

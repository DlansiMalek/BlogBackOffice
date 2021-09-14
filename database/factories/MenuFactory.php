<?php
/** @var \Illuminate\Database\Eloquent\Factory $factory */

namespace Database\Factories;
use App\Models\Menu;
use Faker\Generator as Faker;

$factory->define(Menu::class, function (Faker $faker) {
    return [
        'key' => $faker->word,
        'icon' => $faker->word,
        'url'=>$faker->word,
        'index'=>$faker->numberBetween(0, 100),
        'show_after_reload'=>$faker->numberBetween(0, 1)

    ];
});


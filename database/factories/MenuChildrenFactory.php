<?php
/** @var \Illuminate\Database\Eloquent\Factory $factory */

namespace Database\Factories;
use App\Models\MenuChildren;
use Faker\Generator as Faker;

$factory->define(MenuChildren::class, function (Faker $faker) {
    return [
        'key' => $faker->word,
        'icon' => $faker->word,
        'url'=>$faker->word,
        'index'=>$faker->numberBetween(0, 100),
        
    ];
});
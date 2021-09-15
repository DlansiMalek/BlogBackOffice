<?php
/** @var \Illuminate\Database\Eloquent\Factory $factory */

namespace Database\Factories;
use App\Models\Project;
use Faker\Generator as Faker;
use App\Services\Utils;


$factory->define(Project::class, function (Faker $faker) {
    return [
        'nom' => $faker->word,
        'date' => $faker->dateTime(),
        'lien'=>$faker->word,
        'project_img'=>Utils::generateCode(0, 15) . ".png"
    ];
});


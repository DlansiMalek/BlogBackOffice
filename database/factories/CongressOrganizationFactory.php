<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\CongressOrganization;
use Faker\Generator as Faker;

$factory->define(CongressOrganization::class, function (Faker $faker) {
    return [
        'montant' => $faker->numberBetween(100, 1000)
    ];
});

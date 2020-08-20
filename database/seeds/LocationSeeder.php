<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Location')->insert([
            'lng' => 10,
            'lat' => 34,
            'address' => 'adresse test 1',
            'congress_id' => 1,
            'city_id' => 1
        ]);
        DB::table('Location')->insert([
            'lng' => 11,
            'lat' => 33,
            'address' => 'adresse test 2',
            'congress_id' => 2,
            'city_id' => 2
        ]);
        DB::table('Location')->insert([
            'lng' => 10.5,
            'lat' => 33.5,
            'address' => 'adresse test 3',
            'congress_id' => 3,
            'city_id' => 3
        ]);
    }
}
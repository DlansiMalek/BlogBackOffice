<?php

namespace Database\Seeders;
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
            'lng' => 10.26136550000001,
            'lat' => 36.8501833,
            'address' => 'adresse test 1',
            'congress_id' => 1,
            'city_id' => 1
        ]);
        DB::table('Location')->insert([
            'lng' => 10.26136550000001,
            'lat' => 36.8501833,
            'address' => 'adresse test 2',
            'congress_id' => 2,
            'city_id' => 2
        ]);
        DB::table('Location')->insert([
            'lng' => 10.26136550000001,
            'lat' => 36.8501833,
            'address' => 'adresse test 3',
            'congress_id' => 3,
            'city_id' => 3
        ]);
    }
}

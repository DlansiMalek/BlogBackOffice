<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LocationSeedTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Location')->insert([
            'lng' => 98,
            'lat' => 58,
            'adress'=> 'adresse test',
            'congress_id'=>1,
            'city_id'=> 3350
        ]);
    }
}

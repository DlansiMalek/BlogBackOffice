<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PackAdminSeedTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Pack_Admin')->insert([
            'name' => 'Pack Admin test 1',
            'type' => 'Event',
            'capacity' => 50,
            'price' => 30.000,
            'nbr_events' => 5
        ]);
        DB::table('Pack_Admin')->insert([
            'name' => 'Pack Admin test 2',
            'type' => 'Duree',
            'capacity' => 50,
            'price' => 20.000,
            'nbr_days' => 5
        ]);
        DB::table('Pack_Admin')->insert([
            'name' => 'Pack Admin test 3',
            'type' => 'Demo',
            'capacity' => -1,
            'price' => 0,
            'nbr_events' => 5
        ]);

    }

}

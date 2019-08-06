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
            'name' => 'Pack Admin test',
            'type' => 'Event',
            'capacity' => 50,
            'price' => 30.000,
            'nbr_events' => 5
        ]);
    }
}

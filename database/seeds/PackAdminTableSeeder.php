<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PackAdminTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('pack_admin')->insert([
            'name' => 'testpack1',
            'type'=>'Event',
            'capacity'=>'50',
            'price'=>'30.000',
            'nbr_events'=>'5'
        ]);
    }
}

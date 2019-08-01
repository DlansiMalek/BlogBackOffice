<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccessSeedTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Access')->insert([
            'name' => 'Access Test',
            'price' => 40,
            'duration' => 90,
            'max_places' => 120,
            'room' => 'Room 1 Arena',
            'start_date' => date("Y-m-d"),
            'end_date' => date("Y-m-d"),
            'show_in_register' => 1,
            'congress_id' => 1,
            'topic_id' => 1,
            'access_type_id' => 2
        ]);
    }
}

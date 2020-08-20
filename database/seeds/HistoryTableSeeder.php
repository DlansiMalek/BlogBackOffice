<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HistoryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('History_Pack')->insert([
            'status' => 0,
            'nbr_events' => 0,
            'pack_admin_id' => 1,
            'admin_id' => 3,
            'start_date' => date("Y-m-d"),
            'end_date' => date("Y-m-d")
        ]);
        DB::table('History_Pack')->insert([
            'status' => 0,
            'nbr_events' => 3,
            'pack_admin_id' => 2,
            'admin_id' => 3,
            'start_date' => date("Y-m-d"),
            'end_date' => date("Y-m-d")
        ]);
        DB::table('History_Pack')->insert([
            'status' => 1,
            'nbr_events' => 0,
            'pack_admin_id' => 3,
            'admin_id' => 3,
            'start_date' => date("Y-m-d"),
            'end_date' => date("Y-m-d")
        ]);
    }
}

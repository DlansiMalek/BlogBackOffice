<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConfigSelectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Config_Selection')->insert([
            'num_evaluators' => 3,
            'congress_id' => 1,
            'selection_type' => 0,
            'start_date' => date("Y-m-d"),
            'end_date' => date("Y-m-d"),
        ]);
        DB::table('Config_Selection')->insert([
            'num_evaluators' => 2,
            'congress_id' => 2,
            'selection_type' => 0,
            'start_date' => date("Y-m-d"),
            'end_date' => date("Y-m-d"),
        ]);
        DB::table('Config_Selection')->insert([
            'num_evaluators' => 1,
            'congress_id' => 3,
            'selection_type' => 0,
            'start_date' => date("Y-m-d"),
            'end_date' => date("Y-m-d"),
        ]);
    }
}

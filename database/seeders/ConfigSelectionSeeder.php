<?php

namespace Database\Seeders;
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
        DB::table('Evaluation_Inscription')->insert([
            'admin_id' => 1,
            'num_evaluators' => 3,
            'congress_id' => 1,
            'selection_type' => 0,
            'start_date' => date("Y-m-d"),
            'end_date' => date("Y-m-d"),
        ]);
    }
}

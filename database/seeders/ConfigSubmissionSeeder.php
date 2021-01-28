<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConfigSubmissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Config_Submission')->insert([
            'congress_id' => 1,
            'max_words' => 200,
            'num_evaluators' => 3,
            'start_submission_date' => date("Y-m-d"),
            'end_submission_date' => date("Y-m-d"),
        ]);
        DB::table('Config_Submission')->insert([
            'congress_id' => 2,
            'max_words' => 250,
            'num_evaluators' => 1,
            'start_submission_date' => date("Y-m-d"),
            'end_submission_date' => date("Y-m-d"),
        ]);
        DB::table('Config_Submission')->insert([
            'congress_id' => 3,
            'max_words' => 300,
            'num_evaluators' => 2,
            'start_submission_date' => date("Y-m-d"),
            'end_submission_date' => date("Y-m-d"),
        ]);
    }
}

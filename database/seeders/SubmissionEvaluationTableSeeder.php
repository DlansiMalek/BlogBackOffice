<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubmissionEvaluationTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Submission_Evaluation')->insert([
            'admin_id'=>1,
            'submission_id'=>1,
            'note'=>7
        ]);
        DB::table('Submission_Evaluation')->insert([
            'admin_id' => 2,
            'submission_id' => 2,
            'note' => 11
        ]);
        DB::table('Submission_Evaluation')->insert([
            'admin_id' => 3,
            'submission_id' => 3,
            'note'=> 13
        ]);
    }
}

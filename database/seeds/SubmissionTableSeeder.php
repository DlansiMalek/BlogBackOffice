<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubmissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Submission')->insert([
            'title' => 'submission_title',
            'type' => 'Scientifique',
            'description' => 'submission_description',
            'global_note' => 13,
            'status' => 1,
            'theme_id' => 1,
            'congress_id' => 1,
            'user_id' => 1
        ]);
    }
}

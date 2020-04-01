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
            'title'=>'submission_title',
            'type'=>'Scientifique',
            'prez_type'=>'workshop',
            'description'=>'submission_description',
            'global_note'=>13,
            'status'=>1
        ]);
    }
}

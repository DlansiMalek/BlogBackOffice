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
            'title' => 'submission test 1',
            'type' => 'Scientifique',
            'description' => 'submission description 1',
            'global_note' => 13,
            'status' => 1,
            'theme_id' => 1,
            'congress_id' => 1,
            'user_id' => 1
        ]);
        DB::table('Submission')->insert([
            'title' => 'submission test 2',
            'type' => 'Sport',
            'description' => 'submission description 2',
            'global_note' => 14,
            'status' => 0,
            'theme_id' => 2,
            'congress_id' => 2,
            'user_id' => 2
        ]);
        DB::table('Submission')->insert([
            'title' => 'submission test 3',
            'type' => 'Litterature',
            'description' => 'submission description 3',
            'global_note' => 9,
            'status' => -1,
            'theme_id' => 3,
            'congress_id' => 3,
            'user_id' => 3
        ]);
    }
}

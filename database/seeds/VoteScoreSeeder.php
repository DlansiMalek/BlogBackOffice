<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VoteScoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Vote_Score')->insert([
            'score' => 3,
            'num_user_vote' => 1,
            'access_vote_id'=> 1,
            'user_id' => 1
        ]);
        DB::table('Vote_Score')->insert([
            'score' => 2,
            'num_user_vote' => 2,
            'access_vote_id'=> 2,
            'user_id' => 2
        ]);
    }
}

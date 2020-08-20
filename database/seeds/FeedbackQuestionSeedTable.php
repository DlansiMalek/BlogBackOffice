<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FeedbackQuestionSeedTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Feedback_Question')->insert([
            'question' => "Test question feedback 1",
            'isText' => 1,
            'congress_id' => 1
        ]);
        DB::table('Feedback_Question')->insert([
            'question' => "Test question feedback 2",
            'isText' => 1,
            'congress_id' => 2
        ]);
        DB::table('Feedback_Question')->insert([
            'question' => "Test question feedback 3",
            'isText' => 1,
            'congress_id' => 3
        ]);
    }
}

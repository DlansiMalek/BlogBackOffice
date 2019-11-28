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
            'question' => "Test question feedback",
            'isText' => 1,
            'congress_id' => 1
        ]);
    }
}

<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FeedbackResponseSeedTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Feedback_Response')->insert([
            'feedback_value_id' => 2,
            'feedback_question_id' => 1,
            'user_id' => 1
        ]);
    }
}

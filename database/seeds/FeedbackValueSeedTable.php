<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FeedbackValueSeedTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Feedback_Value')->insert([
            'value' => "Value Feedback 1",
            'feedback_question_id' => 1,
        ]);

        DB::table('Feedback_Value')->insert([
            'value' => "Value Feedback 2",
            'feedback_question_id' => 1,
        ]);

        DB::table('Feedback_Value')->insert([
            'value' => "Value Feedback 3",
            'feedback_question_id' => 1,
        ]);
    }
}

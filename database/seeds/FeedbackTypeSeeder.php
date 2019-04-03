<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FeedbackTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Feedback_Question_Type')->insert([
            'name' => 'text',
            'display_name' => 'Texte',
        ]);

        DB::table('Feedback_Question_Type')->insert([
            'name' => 'choice',
            'display_name' => 'Choix',
        ]);
    }
}

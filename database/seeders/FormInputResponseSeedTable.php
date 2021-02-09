<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FormInputResponseSeedTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Form_Input_Response')->insert([
            'response' => 'response test 1',
            'user_id' => 1,
            'form_input_id' => 1
        ]);

        DB::table('Form_Input_Response')->insert([
            'user_id' => 1,
            'form_input_id' => 2
        ]);
        DB::table('Form_Input_Response')->insert([
            'response' => 'response test 2',
            'user_id' => 2,
            'form_input_id' => 2
        ]);
        DB::table('Form_Input_Response')->insert([
            'response' => 'response test 3',
            'user_id' => 3,
            'form_input_id' => 2
        ]);
    }
}

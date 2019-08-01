<?php

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
            'response' => 'response test',
            'user_id' => 1,
            'form_input_id' => 1
        ]);

        DB::table('Form_Input_Response')->insert([
            'user_id' => 1,
            'form_input_id' => 2
        ]);
    }
}

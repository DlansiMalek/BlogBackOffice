<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FormInputValueSeedTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Form_Input_Value')->insert([
            'value' => 'option 1',
            'form_input_id' => 1
        ]);
        DB::table('Form_Input_Value')->insert([
            'value' => 'option 2',
            'form_input_id' => 1
        ]);
        DB::table('Form_Input_Value')->insert([
            'value' => 'option 3',
            'form_input_id' => 1
        ]);
        DB::table('Form_Input_Value')->insert([
            'value' => 'option 4',
            'form_input_id' => 1
        ]);
        DB::table('Form_Input_Value')->insert([
            'value' => 'option 5',
            'form_input_id' => 1
        ]);
    }
}

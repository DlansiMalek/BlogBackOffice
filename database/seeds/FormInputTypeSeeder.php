<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FormInputTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Form_Input_Type')->insert([
            'name' => 'text',
        ]);

        DB::table('Form_Input_Type')->insert([
            'name' => 'textarea',
        ]);

        DB::table('Form_Input_Type')->insert([
            'name' => 'date',
        ]);

        DB::table('Form_Input_Type')->insert([
            'name' => 'time',
        ]);

        DB::table('Form_Input_Type')->insert([
            'name' => 'datetime',
        ]);

        DB::table('Form_Input_Type')->insert([
            'name' => 'checklist',
        ]);

        DB::table('Form_Input_Type')->insert([
            'name' => 'multiselect',
        ]);

        DB::table('Form_Input_Type')->insert([
            'name' => 'select',
        ]);

        DB::table('Form_Input_Type')->insert([
            'name' => 'radio',
        ]);

        DB::table('Form_Input_Type')->insert([
            'name' => 'file',
        ]);

        DB::table('Form_Input_Type')->insert([
            'name' => 'multifile',
        ]);
    }
}

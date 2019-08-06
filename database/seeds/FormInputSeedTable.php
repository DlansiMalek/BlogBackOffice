<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FormInputSeedTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Form_Input')->insert([
            'label' => 'Question test',
            'congress_id' => 1,
            'form_input_type_id' => 1
        ]);

        DB::table('Form_Input')->insert([
            'label' => 'Question test select',
            'congress_id' => 1,
            'form_input_type_id' => 8
        ]);
    }
}

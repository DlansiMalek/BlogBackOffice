<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ResponseValueSeedTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Response_Value')->insert([
            'form_input_response_id' => 2,
            'form_input_value_id' => 3
        ]);
        DB::table('Response_Value')->insert([
            'form_input_response_id' => 1,
            'form_input_value_id' => 2
        ]);
    }
}

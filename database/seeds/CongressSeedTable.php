<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CongressSeedTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Congress')->insert([
            'name' => 'Congress Test 1',
            'start_date' => date("Y-m-d"),
            'end_date' => date("Y-m-d"),
            'price' => '200',
            'congress_type_id' => 1
        ]);
        DB::table('Congress')->insert([
            'name' => 'Congress Test 2',
            'start_date' => date("Y-m-d"),
            'end_date' => date("Y-m-d"),
            'price' => '0',
            'congress_type_id' => 2
        ]);
        DB::table('Congress')->insert([
            'name' => 'Congress Test 3',
            'start_date' => date("Y-m-d"),
            'end_date' => date("Y-m-d"),
            'price' => '0',
            'congress_type_id' => 3
        ]);
    }
}

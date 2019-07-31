<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CongressTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Congress')->insert([
            'name' => 'Congress Test',
            'start_date' => date("Y-m-d"),
            'end_date' => date("Y-m-d"),
            'price' => '200'
        ]);
    }
}

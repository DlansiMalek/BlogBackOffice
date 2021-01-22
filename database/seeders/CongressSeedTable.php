<?php

namespace Database\Seeders;
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
    }
}

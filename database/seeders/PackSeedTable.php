<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PackSeedTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Pack')->insert([
            'label' => 'Pack test',
            'description' => 'description pack',
            'price' => 250,
            'congress_id' => 1
        ]);
    }
}

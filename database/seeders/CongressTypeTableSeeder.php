<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CongressTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Congress_Type')->insert([
            'congress_type_id' => 1,
            'label' => 'Payant'
        ]);

        DB::table('Congress_Type')->insert([
            'congress_type_id' => 2,
            'label' => 'Gratuit avec sélection'
        ]);

        DB::table('Congress_Type')->insert([
            'congress_type_id' => 3,
            'label' => 'Gratuit sans selection'
        ]);
    }
}

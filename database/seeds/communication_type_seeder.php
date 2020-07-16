<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class communication_type_seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Communication_Type')->insert([
            'label' => 'communication orale',
            'abrv' => 'CO'
        ]);

        DB::table('Communication_Type')->insert([
            'label' => 'communication affichée',
            'abrv' => 'CO'
        ]);
    }
}

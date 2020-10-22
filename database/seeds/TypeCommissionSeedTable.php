<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TypeCommissionSeedTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Type_Commission')->insert([
            'type_commission_id' => 1,
            'label' => 'Nombre des inscrits',
        ]);

        DB::table('Type_Commission')->insert([
            'type_commission_id' => 2,
            'label' => 'Nombre des présents',
        ]);


    }
}

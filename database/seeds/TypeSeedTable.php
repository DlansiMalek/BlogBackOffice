<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TypeSeedTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Type')->insert([
            'type_id' => 1,
            'name' => 'Forfait',
        ]);

        DB::table('Type')->insert([
            'type_id' => 2,
            'name' => 'Commission sur le nombre des inscrits',
        ]);

        DB::table('Type')->insert([
            'type_id' => 3,
            'name' => 'Commission sur le nombre des présents',
        ]);

        DB::table('Type')->insert([
            'type_id' => 4,
            'name' => 'Abonnement',
        ]);

    }
}

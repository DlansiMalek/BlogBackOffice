<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OffreTypeSeedTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Offre_Type')->insert([
            'offre_type_id' => 1,
            'name' => 'Forfait'
        ]);

        DB::table('Offre_Type')->insert([
            'offre_type_id' => 2,
            'name' => 'Commission sur le nombre des inscrits'
        ]);

        DB::table('Offre_Type')->insert([
            'offre_type_id' => 3,
            'name' => 'Commission sur le nombre des présents'
        ]);

        DB::table('Offre_Type')->insert([
            'offre_type_id' => 4,
            'name' => 'Abonnement'
        ]);

    }
}

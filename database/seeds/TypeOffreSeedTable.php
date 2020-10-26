<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TypeOffreSeedTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Type_Offre')->insert([
            'type_offre_id' => 1,
            'name' => 'Forfait',
        ]);

        DB::table('Type_Offre')->insert([
            'type_offre_id' => 2,
            'name' => 'Commission ',
        ]);

        DB::table('Type_Offre')->insert([
            'type_offre_id' => 3,
            'name' => 'Abonnement',
        ]);

    }
}

<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OffreSeedTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Offre')->insert([
            'offre_id' => 1,
            'name' => 'Forfait',
        ]);

        DB::table('Offre')->insert([
            'offre_id' => 2,
            'name' => 'Commission ',
        ]);

        DB::table('Offre')->insert([
            'offre_id' => 3,
            'name' => 'Abonnement',
        ]);

    }
}

<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CongressOrganizationSeedTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Congress_Organization')->insert([
            'congress_id' => 1,
            'organization_id' => 1,
            'montant' => 152.00
        ]);
        DB::table('Congress_Organization')->insert([
            'congress_id' => 2,
            'organization_id' => 2,
            'montant' => 200.00
        ]);
        DB::table('Congress_Organization')->insert([
            'congress_id' => 3,
            'organization_id' => 3,
            'montant' => 320.00
        ]);
    }
}

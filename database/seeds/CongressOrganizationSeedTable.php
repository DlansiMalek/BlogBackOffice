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
    }
}

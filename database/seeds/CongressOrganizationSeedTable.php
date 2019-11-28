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
        DB::table('CongressOrganization')->insert([
            'congress_id' => 1,
            'organization_id' => 1
        ]);
    }
}

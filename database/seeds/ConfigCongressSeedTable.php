<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConfigCongressSeedTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Config_Congress')->insert([
            'has_payment' => 1,
            'voting_token' => "44dcda18-31d3-4b79-bcfe-aa2003e36a39",
            'congress_id' => 1
        ]);
    }
}

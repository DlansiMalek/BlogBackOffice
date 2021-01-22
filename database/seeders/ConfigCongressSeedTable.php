<?php

namespace Database\Seeders;
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
            'voting_token' => "feb1de9a-0e87-4606-bb18-7a44ef9d175c",
            'congress_id' => 1
        ]);
    }
}

<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttestationRequestSeedTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Attestation_Request')->insert([
            'user_id' => 1,
            'access_id' => 1,
            'congress_id' => 1
        ]);
        DB::table('Attestation_Request')->insert([
            'user_id' => 2,
            'access_id' => 2,
            'congress_id' => 2
        ]);
        DB::table('Attestation_Request')->insert([
            'user_id' => 3,
            'access_id' => 3,
            'congress_id' => 3
        ]);
        
    }
}

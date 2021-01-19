<?php

namespace Database\Seeders;
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
    }
}

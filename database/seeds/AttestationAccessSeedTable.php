<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttestationAccessSeedTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Attestation_Access')->insert([
            'access_id' => 1,
            'attestation_generator_id' => "5ba40e537e3f6c0001ff19a9"
        ]);
    }
}

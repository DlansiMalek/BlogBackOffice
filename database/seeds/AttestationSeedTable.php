<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttestationSeedTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Attestation')->insert([
            'attestation_generator_id_blank' => '5baf5fc77e3f6c0001ff19bb',
            'congress_id' => 1,
            'attestation_generator_id' => "5ba40e537e3f6c0001ff19a9"
        ]);
    }
}

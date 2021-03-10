<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttestationDiversSeedTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Attestation_Divers')->insert([
            'attestation_generator_id' => '5be12f9a1f479c0001c7e87d',
            'attestation_type_id' => 1,
            'congress_id' => 1
        ]);

        DB::table('Attestation_Divers')->insert([
            'attestation_generator_id' => '5be12f9a1f479c0001c7e87d',
            'attestation_type_id' => 2,
            'congress_id' => 1
        ]);
    }
}

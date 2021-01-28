<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttestationTypeSeedTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Attestation_Type')->insert([
            'label' => 'Remerciements'
        ]);
        DB::table('Attestation_Type')->insert([
            'label' => 'Conférencier'
        ]);
    }
}

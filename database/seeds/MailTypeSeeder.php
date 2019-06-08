<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MailTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Mail_Type')->insert([
            'name' => 'registration',
            'display_name' => 'Inscription',
        ]);

        DB::table('Mail_Type')->insert([
            'name' => 'payment',
            'display_name' => 'Paiement',
        ]);

        DB::table('Mail_Type')->insert([
            'name' => 'confirmation',
            'display_name' => 'Confirmation',
        ]);
        DB::table('Mail_Type')->insert([
            'name' => 'attestation',
            'display_name' => 'Attestation',
        ]);

        DB::table('Mail_Type')->insert([
            'name' => 'Custom',
            'display_name' => 'Personnalisé',
        ]);

        DB::table('Mail_Type')->insert([
            'name' => 'organization',
            'display_name' => 'Organization',
        ]);

        DB::table('Mail_Type')->insert([
            'name' => 'subvention',
            'display_name' => 'Subvention',
        ]);
        DB::table('Mail_Type')->insert([
            'name' => 'upload',
            'display_name' => 'Téléchargement d\'un reçu bancaire'
        ]);

        DB::table('Mail_Type')->insert([
            'name' => 'free',
            'display_name' => 'Gratuité',
        ]);
    }
}

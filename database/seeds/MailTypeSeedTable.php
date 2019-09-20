<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MailTypeSeedTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Mail_Type')->insert([
            'mail_type_id' => 1,
            'name' => 'inscription',
            'display_name' => 'Inscription',
        ]);

        DB::table('Mail_Type')->insert([
            'mail_type_id' => 2,
            'name' => 'paiement',
            'display_name' => 'Paiement',
        ]);

        DB::table('Mail_Type')->insert([
            'mail_type_id' => 3,
            'name' => 'attestation',
            'display_name' => 'Attestation',
        ]);
        DB::table('Mail_Type')->insert([
            'mail_type_id' => 4,
            'name' => 'custom',
            'display_name' => 'Personnalisé',
        ]);

        DB::table('Mail_Type')->insert([
            'mail_type_id' => 5,
            'name' => 'confirmation',
            'display_name' => 'Confirmation',
        ]);


        DB::table('Mail_Type')->insert([
            'mail_type_id' => 6,
            'name' => 'organization',
            'display_name' => "Création d'un organisme",
        ]);

        DB::table('Mail_Type')->insert([
            'mail_type_id' => 7,
            'name' => 'subvention',
            'display_name' => 'Subvention',
        ]);
        DB::table('Mail_Type')->insert([
            'mail_type_id' => 8,
            'name' => 'upload',
            'display_name' => 'Téléchargement d\'un reçu bancaire'
        ]);

        DB::table('Mail_Type')->insert([
            'mail_type_id' => 9,
            'name' => 'free',
            'display_name' => 'Inscription Gratuite',
        ]);
        DB::table('Mail_Type')->insert([
            'mail_type_id' => 10,
            'name' => 'organizer_creation',
            'display_name' => "Creation d'un organisateur",
        ]);
    }
}

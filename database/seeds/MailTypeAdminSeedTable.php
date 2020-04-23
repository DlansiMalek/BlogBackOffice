<?php

use Illuminate\Database\Seeder;

class MailTypeAdminSeedTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('Mail_Type_Admin')->insert([
            'mail_type_admin_id' => 1,
            'name' => 'inscription',
            'display_name' => 'Inscription',
        ]);

        DB::table('Mail_Type_Admin')->insert([
            'mail_type_admin_id' => 2,
            'name' => 'paiement',
            'display_name' => 'Paiement',
        ]);

        DB::table('Mail_Type_Admin')->insert([
            'mail_type_admin_id' => 3,
            'name' => 'attestation',
            'display_name' => 'Attestation',
        ]);
        DB::table('Mail_Type_Admin')->insert([
            'mail_type_admin_id' => 4,
            'name' => 'custom',
            'display_name' => 'Personnalisé',
        ]);

        DB::table('Mail_Type_Admin')->insert([
            'mail_type_admin_id' => 5,
            'name' => 'confirmation',
            'display_name' => 'Confirmation',
        ]);


        DB::table('Mail_Type_Admin')->insert([
            'mail_type_admin_id' => 6,
            'name' => 'organization',
            'display_name' => 'Organization',
        ]);

        DB::table('Mail_Type_Admin')->insert([
            'mail_type_admin_id' => 7,
            'name' => 'subvention',
            'display_name' => 'Subvention',
        ]);
        DB::table('Mail_Type_Admin')->insert([
            'mail_type_admin_id' => 8,
            'name' => 'upload',
            'display_name' => 'Téléchargement d\'un reçu bancaire'
        ]);

        DB::table('Mail_Type_Admin')->insert([
            'mail_type_admin_id' => 9,
            'name' => 'free',
            'display_name' => 'Inscription Gratuite',
        ]);
        DB::table('Mail_Type_Admin')->insert([
            'mail_type_admin_id' => 10,
            'name' => 'acitivation',
            'display_name' => 'Email de confirmation',
        ]);
    }
}

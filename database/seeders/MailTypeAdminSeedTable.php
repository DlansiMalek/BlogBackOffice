<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MailTypeAdminSeedTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::table('Mail_Type_Admin')->insert([
            'mail_type_admin_id' => 1,
            'name' => 'confirmation',
            'display_name' => 'Email de confirmation'
        ]);

        DB::table('Mail_Type_Admin')->insert([
            'mail_type_admin_id' => 2,
            'name' => 'creation_admin',
            'display_name' => 'Création de compte Admin'
        ]);

        DB::table('Mail_Type_Admin')->insert([
            'mail_type_admin_id' => 3,
            'name' => 'forget_password',
            'display_name' => 'Réinitialiser le Mot de passe'
        ]);

        DB::table('Mail_Type_Admin')->insert([
            'mail_type_admin_id' => 4,
            'name' => 'reset_password_success',
            'display_name' => 'Mot de passe Réinitialiser avec succès'
        ]);

        DB::table('Mail_Type_Admin')->insert([
            'mail_type_admin_id' => 5,
            'name' => 'custom',
            'display_name' => 'Personalisé'
        ]);
        DB::table('Mail_Type_Admin')->insert([
            'mail_type_admin_id' => 6,
            'name' => 'update_profile',
            'display_name' => 'Update profile'
        ]);
        DB::table('Mail_Type_Admin')->insert([
            'mail_type_admin_id' => 7,
            'name' => 'create_offre',
            'display_name' => 'Création d\'un offre'
        ]);
        DB::table('Mail_Type_Admin')->insert([
            'mail_type_admin_id' => 8,
            'name' => 'contact_us',
            'display_name' => 'Contacter nous'
        ]);
    }
}

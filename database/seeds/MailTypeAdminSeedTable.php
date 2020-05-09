<?php

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
            'name' => 'creation',
            'display_name' => 'Création de compte Admin',
        ]);
    }
}

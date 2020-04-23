<?php

use Illuminate\Database\Seeder;

class MailAdminSeedTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('Mail_Admin')->insert([
            'object' => "Confirmation d'incsription ",
            'template' => '<p>Votre compte a été créer, veuillez cliquer sur ce lien afin de le  valider.</p><p><a href="{{%24activationLink}}">Lien</a></p>',

            'mail_type_admin_id' => 10
        ]);
    }
}

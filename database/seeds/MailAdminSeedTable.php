<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MailAdminSeedTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Mail_Admin')->insert([
            'object' => "Confirmation d'incsription ",
            'template' => '<p>Votre compte a été créer, veuillez cliquer sur ce lien afin de le  valider.</p><p><a href="{{$activationLink}}">Lien</a></p>',
            'mail_type_admin_id' => 1
        ]);

        DB::table('Mail_Admin')->insert([
            'object' => "Compte Organisateur Eventizer",
            'template' => '<p>Voici vos coordonnées : </p><ul><li>Email: {{$admin-&gt;email}}</li><li>Password : {{$admin-&gt;passwordDecrypt}}</li></ul><p>Accéedez à la platforme via :<a href="{{$backOfficeLink}}" target="_blank" rel="noopener noreferrer">Link</a></p>',
            'mail_type_admin_id' => 2
        ]);
    }
}

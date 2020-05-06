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
            'object' => "Test object",
            'template' => '<p>Veuillez cliquer sur ce lien afin de valider votre paiement.</p><p><a href="{{%24link}}">Lien</a></p>',

            'mail_type_admin_id' => 1
        ]);
        
        DB::table('Mail_Admin')->insert([
            'object' => "Credentials",
            'template' => '<p>Voici vos coordonnées : </p><ul><li>Email: {{$email}}</li><li>Password : {{$password}}</li></ul><p>Accéedez à la platforme via :<a href="{{$linkBackOffice}}" target="_blank" rel="noopener noreferrer">Link</a></p>',
            'mail_type_admin_id' => 2
        ]);
    }
}

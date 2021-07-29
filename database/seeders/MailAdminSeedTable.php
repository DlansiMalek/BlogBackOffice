<?php

namespace Database\Seeders;
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
            'template' => '<p>Voici vos coordonnées : </p><ul><li>Email: {{$admin-&gt;email}}</li><li>Password : {{$admin-&gt;passwordDecrypt}}</li></ul><p>Accéedez à la platforme via :<a href="{{$linkBackOffice}}" target="_blank" rel="noopener noreferrer">Link</a></p>',
            'mail_type_admin_id' => 2
        ]);
        DB::table('Mail_Admin')->insert([
            'object' => "Réinitialiser le Mot de passe",
            'template' => '<p>Veuillez réinitialiser votre mot de passe à partir de ce </p><p><a href="{{$activationLink}}">Lien</a></p>',
            'mail_type_admin_id' => 3
        ]);
        DB::table('Mail_Admin')->insert([
            'object' => "Mot de passe réinitialiser",
            'template' => '<p>Mot de passe réinitialiser avec succès</p>',
            'mail_type_admin_id' => 4
        ]);
        DB::table('Mail_Admin')->insert([
            'object' => "Mise à jour de votre profil",
            'template' => '<p>Votre profil à été mis à jour avec succès</p>',
            'mail_type_admin_id' => 6
        ]);
        DB::table('Mail_Admin')->insert([
            'object' => "Paiement de votre offre",
            'template' => '<p>Veuillez cliquer sur ce lien afin de valider votre paiement: <a href="{{$paymentLink}}">Lien</a></p>',
            'mail_type_admin_id' => 7
        ]);
        DB::table('Mail_Admin')->insert([
            'object' => "Contacter nous",
            'template' => '<p>Monsieur/Madame {{$contact-&gt;user_name}}</p><p>vous communique le message suivant:</p><p>{{$contact-&gt;message}}</p><p>veuillez repondre via cette adresse :{{$contact-&gt;email}}</p>',
            'mail_type_admin_id' => 8
        ]);
    }
}

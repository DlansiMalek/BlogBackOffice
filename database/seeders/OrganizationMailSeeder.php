<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class OrganizationMailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Mail')->insert([
            'object' => 'Création d’un organisme',
            'template' =>
            '<p> Cher représentant,</p>
            <p>Le comité d’organisation de l’évènement :
            a validé votre compte afin d’accéder à votre espace privé.
            Vous trouverez ci-dessous le lien d’accès ainsi que vos informations d’authentification :</p>
            <ul>
            <li>
            Lien:{{$linkBackOffice}}
            </li>
            <li>Login: {{$admin-&gt;email}} </li>
            <li>Password: {{$admin-&gt;passwordDecrypt}}</li>
            </ul>
            <p><br></p>
            <p>En cas de besoin, vous pouvez joindre le support technique de la plateforme Eventizer par email:</p>
            <a href="mailto:support@eventizer.io" rel="noopener noreferrer" target="_blank" style="color: rgb(17, 85, 204);">support@eventizer.io</a>',
            'mail_type_id' => 6
        ]);
    }
}

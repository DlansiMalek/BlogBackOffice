<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MailContactUsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Mail_Admin')->where('mail_type_admin_id', '=', 8)->delete();
        DB::table('Mail_Admin')->insert([
            'object' => "Contactez-nous",
            'template' => '<p>Monsieur/Madame {{$contact-&gt;user_name}}</p>
            <p>Vous communique le message suivant:</p><p>{{$contact-&gt;message}}</p>
            <p>Veuillez répondre via cette adresse :{{$contact-&gt;email}}</p>
            <p>Ou bien via ce numéro de téléphone:{{$contact-&gt;mobile}}</p>',
            'mail_type_admin_id' => 8
        ]);
    }
}

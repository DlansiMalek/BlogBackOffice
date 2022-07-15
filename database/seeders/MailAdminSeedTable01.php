<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MailAdminSeedTable01 extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Mail_Admin')->insert([
            'object' => "Acceptation du votre demande de création de Landing Page",
            'template' => '<p>votre demande de création de landing page est acceptée</p><ul></ul><p>Accéedez à votre Landing page via : <a href="{{$linkBackOffice}}">Lien</a></p>',
            'mail_type_admin_id' => 9
        ]);
        DB::table('Mail_Admin')->insert([
            'object' => "Refus de votre demande de création de Landing Page",
            'template' => '<p>votre demande de création de landing page est refusée, vous pouvez réessayer une autre fois </p>',
            'mail_type_admin_id' => 10
        ]);  
    }
}

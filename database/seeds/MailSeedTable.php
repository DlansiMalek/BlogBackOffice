<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MailSeedTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Mail')->insert([
            'object' => "Test object",
            'template' => '<p>Veuillez cliquer sur ce lien afin de valider votre paiement.</p><p><a href="{{%24link}}">Lien</a></p>',
            'congress_id' => 1,
            'mail_type_id' => 1
        ]);
    }
}

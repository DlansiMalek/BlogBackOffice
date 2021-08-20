<?php

namespace Database\Seeders;
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

        DB::table('Mail')->insert([
            'object'=>'room',
            'template' => '<p>Votre room a été créer avec succés, Voici les liens pour y accéder</p><ul><li>lien pour le moderateur <a href="{{$linkModerateur}}" rel="noopener noreferrer" target="_blank">Link</a></li>
            <li>lien pour les invitées <a href="{{$linkInvitees}}" rel="noopener noreferrer" target="_blank">Link</a></li>
            </ul>',
            'mail_type_id'=>12
        ]);

        DB::table('Mail')->insert([
            'object' => 'to change',
            'template' => '<p>to change <p>',
            'mail_type_id' => 25
        ]);

        DB::table('Mail')->insert([
            'object' => 'to change',
            'template' => '<p>to change <p>',
            'mail_type_id' => 26
        ]);
    }
}

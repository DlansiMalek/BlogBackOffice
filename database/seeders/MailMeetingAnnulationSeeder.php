<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MailMeetingAnnulationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Mail')->insert([
            'object' => 'Annulation de la réunion',
            'template' => '<p>Cher participant,</p>
            <p>Nous avons le regret de vous informer que votre rendez-vous avec Mr/Mme {{$user_receiver-&gt;last_name}} {{$user_receiver-&gt;first_name}} a été annulée.</p>
            <p><strong><u>Détail de la demande</u></strong></p>
            <ul><li>{{$user_receiver-&gt;last_name}} {{$user_receiver-&gt;first_name}}</li>
            <li>{{$meeting-&gt;start_date}}</li>
            <p>Vous pouvez toujours consulter votre calendrier de rendez-vous sur votre espace personnel. </p>
            <p>Pour toute demande d’information, veuillez contacter l’équipe support sur l’adresse email suivante : 
                <a href="mailto:support@eventizer.io" rel="noopener noreferrer" target="_blank" style="color: rgb(17, 85, 204);">support@eventizer.io</a>
                </p>',
            'mail_type_id' => 27
        ]);
    }
}

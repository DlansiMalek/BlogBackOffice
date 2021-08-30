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
            'object' => 'room',
            'template' => '<p>Votre room a été créer avec succés, Voici les liens pour y accéder</p><ul><li>lien pour le moderateur <a href="{{$linkModerateur}}" rel="noopener noreferrer" target="_blank">Link</a></li>
            <li>lien pour les invitées <a href="{{$linkInvitees}}" rel="noopener noreferrer" target="_blank">Link</a></li>
            </ul>',
            'mail_type_id' => 12
        ]);

        DB::table('Mail')->insert([
            'object' => 'Demande de réunion',
            'template' => '
            <p>Cher participant,</p>
            <p>Une nouvelle demande de rendez-vous est en attente de votre approbation.</p>
            <p><strong><u>Détail&nbsp;de la demande</u></strong></p>
            <ul>
            <li>{{$user_sender-&gt;last_name}} {{$user_sender-&gt;first_name}}</li>
            <li>{{$meeting-&gt;start_date}}</li>
            </ul>
            <p>Vous pouvez accepter/refuser la demande du rendez-vous en cliquant directement sur le bouton Accepter/refuser ci-dessous ou en visitant votre espace personnel via ce lien 
            <a href="{{$linkFrontOffice}}" rel="noopener noreferrer" target="_blank">Link</a>.
            </p><p>Vous pouvez toujours consulter votre calendrier de rendez-vous sur votre espace personnel. </p>
            <p>&nbsp;</p>
            <p>Pour toute demande d’information, veuillez contacter l’équipe support sur l’adresse email suivante : <a href="mailto:support@eventizer.io" rel="noopener noreferrer" target="_blank" style="color: rgb(17, 85, 204);">support@eventizer.io</a></p>
            <p><span style="color: rgb(17, 85, 204);"><span class="ql-cursor">﻿</span>{{$meetingButtons}}</span></p>',
            'mail_type_id' => 24
        ]);

        DB::table('Mail')->insert([
            'object' => 'Acceptation de la demande de réunion',
            'template' => '<p>Cher participant,</p>
            <p>Votre rendez-vous avec&nbsp;Mr/Mme {{$user_receiver-&gt;last_name}} {{$user_receiver-&gt;first_name}} à été validé et enregistré.</p>
            <p><strong><u>Détail du rendez-vous :</u></strong></p><ul><li>{{$user_receiver-&gt;last_name}} {{$user_receiver-&gt;first_name}}</li>
            <li>{{$meeting-&gt;start_date}}</li></ul><p>Vous pouvez toujours consulter votre calendrier de rendez-vous sur votre espace personnel. </p>
            <p><br></p>
            <p>Pour toute demande d’information, veuillez contacter l’équipe support sur l’adresse email suivante : <a href="mailto:support@eventizer.io" rel="noopener noreferrer" target="_blank" style="color: rgb(17, 85, 204);">support@eventizer.io</a></p>',
            'mail_type_id' => 25
        ]);

        DB::table('Mail')->insert([
            'object' => 'Refus de la demande de réunion',
            'template' => '<p>Cher participant,</p>
            <p>Nous avons le regret de vous informer que votre demande de rendez-vous avec Mr/Mme {{$user_receiver-&gt;last_name}} {{$user_receiver-&gt;first_name}} a été refusée.</p>
            <p><strong><u>Détail de la demande</u></strong></p>
            <ul><li>{{$user_receiver-&gt;last_name}} {{$user_receiver-&gt;first_name}}</li>
            <li>{{$meeting-&gt;start_date}}</li>
            </ul>
            <p>Vous pouvez toujours consulter votre calendrier de rendez-vous sur votre espace personnel. </p>
            <p>Pour toute demande d’information, veuillez contacter l’équipe support sur l’adresse email suivante : 
                <a href="mailto:support@eventizer.io" rel="noopener noreferrer" target="_blank" style="color: rgb(17, 85, 204);">support@eventizer.io</a>
                </p>',
            'mail_type_id' => 26
        ]);
    }
}

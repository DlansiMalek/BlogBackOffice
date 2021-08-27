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
            'object' => 'Cher participant,
            Une nouvelle demande de rendez-vous est en attente de votre approbation.',
            'template' => '<p> Détail  de la demande 
            ●	Nom et prénom du demandeur: 
            ●	Date demandée:
            ●	Heure:
            
            Vous pouvez accepter/refuser la demande du rendez-vous en cliquant directement sur le bouton Accepter/refuser ci-dessous ou en visitant votre espace personnel via ce lien. 
            Vous pouvez toujours consulter votre calendrier de rendez-vous sur votre espace personnel. 
            
            Pour toute demande d’information, veuillez contacter l’équipe support sur l’adresse email suivante : support@eventizer.io
            <p>',
            'mail_type_id' => 24
        ]);

        DB::table('Mail')->insert([
            'object' => 'Cher participant,
            Votre rendez-vous avec  Mr/Mme Nom Prénom à été validé et enregistré.',
            'template' => '<p>Détail du rendez-vous :
            ●	avec: Nom prénom
            ●	Date: 
            ●	Heure: 
            ●	lien direct : 
            
            Vous pouvez toujours consulter votre calendrier de rendez-vous sur votre espace personnel. 
            
            Pour toute demande d’information, veuillez contacter l’équipe support sur l’adresse email suivante : support@eventizer.io
            <p>',
            'mail_type_id' => 25
        ]);

        DB::table('Mail')->insert([
            'object' => 'Cher participant,
            Nous avons le regret de vous informer que votre demande de rendez-vous avec Mr/Mme Nom prénom a été refusée',
            'template' => '<p> Détail de la demande
            ●	Nom et prénom: 
            ●	Date demandée:
            ●	Heure:
            
            Vous pouvez toujours consulter votre calendrier de rendez-vous sur votre espace personnel. 
            
            Pour toute demande d’information, veuillez contacter l’équipe support sur l’adresse email suivante : support@eventizer.io
            <p>',
            'mail_type_id' => 26
        ]);
    }
}

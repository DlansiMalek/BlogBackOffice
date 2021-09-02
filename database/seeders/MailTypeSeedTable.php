<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MailTypeSeedTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Mail_Type')->insert([
            'mail_type_id' => 1,
            'name' => 'inscription',
            'display_name' => 'Inscription'
        ]);

        DB::table('Mail_Type')->insert([
            'mail_type_id' => 2,
            'name' => 'paiement',
            'display_name' => 'Paiement'
        ]);

        DB::table('Mail_Type')->insert([
            'mail_type_id' => 3,
            'name' => 'attestation',
            'display_name' => 'Attestation'
        ]);
        DB::table('Mail_Type')->insert([
            'mail_type_id' => 4,
            'name' => 'custom',
            'display_name' => 'Personnalisé'
        ]);

        DB::table('Mail_Type')->insert([
            'mail_type_id' => 5,
            'name' => 'confirmation',
            'display_name' => 'Confirmation'
        ]);


        DB::table('Mail_Type')->insert([
            'mail_type_id' => 6,
            'name' => 'organization',
            'display_name' => "Création d'un organisme"
        ]);

        DB::table('Mail_Type')->insert([
            'mail_type_id' => 7,
            'name' => 'subvention',
            'display_name' => 'Subvention'
        ]);
        DB::table('Mail_Type')->insert([
            'mail_type_id' => 8,
            'name' => 'upload',
            'display_name' => 'Téléchargement d\'un reçu bancaire'
        ]);

        DB::table('Mail_Type')->insert([
            'mail_type_id' => 9,
            'name' => 'free',
            'display_name' => 'Inscription Gratuite'
        ]);
        DB::table('Mail_Type')->insert([
            'mail_type_id' => 10,
            'name' => 'organizer_creation',
            'display_name' => "Creation d'un organisateur"
        ]);
        DB::table('Mail_Type')->insert([
            'mail_type_id' => 11,
            'name' => 'sondage',
            'display_name' => "Mail de Sondage"
        ]);
        DB::table('Mail_Type')->insert([
            'mail_type_id' => 12,
            'name' => 'room',
            'display_name' => "Creation d\'une room"
        ]);
        DB::table('Mail_Type')->insert([
            'mail_type_id' => 13,
            'name' => 'save_submission',
            'display_name' => "Enregistrer une soumission",
            'type' => "submission"
        ]);
        DB::table('Mail_Type')->insert([
            'mail_type_id' => 14,
            'name' => 'accept_submission',
            'display_name' => "Accepter la soumission",
            'type' => "submission"
        ]);
        DB::table('Mail_Type')->insert([
            'mail_type_id' => 15,
            'name' => 'refuse_submission',
            'display_name' => "Refuser la soumission",
            'type' => "submission"
        ]);
        DB::table('Mail_Type')->insert([
            'mail_type_id' => 16,
            'name' => 'edit_submission',
            'display_name' => "Modification de la soumission",
            'type' => "submission"
        ]);
        DB::table('Mail_Type')->insert([
            'mail_type_id' => 17,
            'name' => 'bloc_edit_submission',
            'display_name' => "Blocage de la modification de la soumission",
            'type' => "submission"
        ]);
        DB::table('Mail_Type')->insert([
            'mail_type_id' => 18,
            'name' => 'Attente_de_fichier',
            'display_name' => "Attente de fichiers",
            'type' => "submission"
        ]);
        DB::table('Mail_Type')->insert([
            'mail_type_id' => 19,
            'name' => 'refus',
            'display_name' => "Refus d\'inscription",
            'type' => "event"
        ]);
        DB::table('Mail_Type')->insert([
            'mail_type_id' => 20,
            'name' => 'file_submitted',
            'display_name' => "Fichiers soumis",
            'type' => "submission"
        ]);

        DB::table('Mail_Type')->insert([
            'mail_type_id' => 21,
            'name' => 'attestation',
            'display_name' => "Mail d'attestation",
            'type' => "submission"
        ]);

        DB::table('Mail_Type')->insert([
            'mail_type_id' => 22,
            'name' => 'attestation_all',
            'display_name' => "Mail d'attestation en masse",
            'type' => "submission"
        ]);
        DB::table('Mail_Type')->insert([
            'mail_type_id' => 23,
            'name' => 'submission_a_reviser',
            'display_name' => "A réviser une soumission",
            'type' => "submission"
        ]);
    }
}

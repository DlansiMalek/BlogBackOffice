<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuChildrenSeedTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Menu_Children')->insert([
            'menu_children_id' => 1,
            'key' => 'CongressParticipant',
            'url' => '/manage-participant/:congress_id/list-participant',
            'menu_id' => 1,
            'index' => 1
        ]);

        DB::table('Menu_Children')->insert([
            'menu_children_id' => 2,
            'key' => 'CongressPresent',
            'url' => '/manage-participant/:congress_id/list-presence',
            'menu_id' => 1,
            'index' => 2
        ]);

        DB::table('Menu_Children')->insert([
            'menu_children_id' => 3,
            'key' => 'ParticipantAccess',
            'url' => '/manage-participant/congress-access',
            'menu_id' => 1,
            'index' => 3
        ]);

        DB::table('Menu_Children')->insert([
            'menu_children_id' => 4,
            'key' => 'ManageGrilles',
            'url' => '/manage-congress/add-grilles',
            'menu_id' => 1,
            'index' => 4
        ]);

        DB::table('Menu_Children')->insert([
            'menu_children_id' => 5,
            'key' => 'AttestationDemand',
            'url' => '/manage-participant/:congress_id/list-participant',
            'menu_id' => 1,
            'index' => 5
        ]);

        DB::table('Menu_Children')->insert([
            'menu_children_id' => 6,
            'key' => 'OrganizerList',
            'url' => '/manage-personnel/list-personnel',
            'menu_id' => 2,
            'index' => 1
        ]);

        DB::table('Menu_Children')->insert([
            'menu_children_id' => 7,
            'key' => 'AddOrganizer',
            'url' => '/manage-personnel/add-personnel',
            'menu_id' => 2,
            'index' => 2
        ]);

        DB::table('Menu_Children')->insert([
            'menu_children_id' => 8,
            'key' => 'ManagePrivilege',
            'url' => '/manage-personnel/list-privilege',
            'menu_id' => 2,
            'index' => 3
        ]);

        DB::table('Menu_Children')->insert([
            'menu_children_id' => 9,
            'key' => 'ManageBadge',
            'url' => '/manage-badge/list-affectation',
            'menu_id' => 3,
            'index' => 1
        ]);

        DB::table('Menu_Children')->insert([
            'menu_children_id' => 10,
            'key' => 'ManageAttestation',
            'url' => '/manage-attestation',
            'menu_id' => 3,
            'index' => 2
        ]);

        DB::table('Menu_Children')->insert([
            'menu_children_id' => 11,
            'key' => 'TrackBadge',
            'url' => '/manage-badge/tracking-badge/:congress_id',
            'menu_id' => 3,
            'index' => 3
        ]);

        DB::table('Menu_Children')->insert([
            'menu_children_id' => 12,
            'key' => 'SubmissionList',
            'url' => '/manage-submission/:congress_id/list-submission',
            'menu_id' => 4,
            'index' => 1
        ]);

        DB::table('Menu_Children')->insert([
            'menu_children_id' => 13,
            'key' => 'MailLists',
            'url' => '/manage-submission/manage-mail/list',
            'menu_id' => 4,
            'index' => 2
        ]);

        DB::table('Menu_Children')->insert([
            'menu_children_id' => 14,
            'key' => 'SubmissionAttestationList',
            'url' => '/manage-submission/:congress_id/list-attestation-submission',
            'menu_id' => 4,
            'index' => 3
        ]);

        DB::table('Menu_Children')->insert([
            'menu_children_id' => 15,
            'key' => 'OrganismList',
            'url' => '/manage-organization/:congress_id/list-organization',
            'menu_id' => 5,
            'index' => 1
        ]);

        DB::table('Menu_Children')->insert([
            'menu_children_id' => 16,
            'key' => 'ParticipantList',
            'url' => '/manage-organization',
            'menu_id' => 5,
            'index' => 2
        ]);

        DB::table('Menu_Children')->insert([
            'menu_children_id' => 17,
            'key' => 'Tracking',
            'url' => '/stats/:congress_id/tracking',
            'menu_id' => 6,
            'index' => 1
        ]);

        DB::table('Menu_Children')->insert([
            'menu_children_id' => 18,
            'key' => 'Chiffres',
            'url' => '/manage-participant/:congress_id/list-statistics',
            'menu_id' => 6,
            'index' => 2
        ]);

        DB::table('Menu_Children')->insert([
            'menu_children_id' => 19,
            'key' => 'Graphes',
            'url' => '/stats',
            'menu_id' => 6,
            'index' => 3
        ]);

        DB::table('Menu_Children')->insert([
            'menu_children_id' => 20,
            'key' => 'MailLists',
            'url' => '/manage-mail/list',
            'menu_id' => 7,
            'index' => 1
        ]);

        DB::table('Menu_Children')->insert([
            'menu_children_id' => 21,
            'key' => 'MailPersonalized',
            'url' => '/manage-mail/personalize',
            'menu_id' => 7,
            'index' => 2
        ]);

        DB::table('Menu_Children')->insert([
            'menu_children_id' => 22,
            'key' => 'FormSondage',
            'url' => '/feedback/form',
            'menu_id' => 8,
            'index' => 1
        ]);

        DB::table('Menu_Children')->insert([
            'menu_children_id' => 23,
            'key' => 'ResultSondage',
            'url' => '/feedback/viz',
            'menu_id' => 8,
            'index' => 2
        ]);

        DB::table('Menu_Children')->insert([
            'menu_children_id' => 24,
            'key' => 'QuizAssociation',
            'url' => '/voting/form',
            'menu_id' => 9,
            'index' => 1
        ]);

        DB::table('Menu_Children')->insert([
            'menu_children_id' => 25,
            'key' => 'QuizResult',
            'url' => '/voting/results',
            'menu_id' => 9,
            'index' => 2
        ]);

        DB::table('Menu_Children')->insert([
            'menu_children_id' => 26,
            'key' => 'TokenVayeVoting',
            'url' => '/voting/token',
            'menu_id' => 9,
            'index' => 3
        ]);

        DB::table('Menu_Children')->insert([
            'menu_children_id' => 27,
            'key' => 'AccessList',
            'url' => '/manage-access/list-access',
            'menu_id' => 10,
            'index' => 1
        ]);

        DB::table('Menu_Children')->insert([
            'menu_children_id' => 28,
            'key' => 'ManageOnlineRooms',
            'url' => '/manage-room/online-list-rooms',
            'menu_id' => 10,
            'index' => 2
        ]);

        DB::table('Menu_Children')->insert([
            'menu_children_id' => 29,
            'key' => 'ManageList',
            'url' => '/manage-stand/list-stand',
            'menu_id' => 11,
            'index' => 1
        ]);

        DB::table('Menu_Children')->insert([
            'menu_children_id' => 30,
            'key' => 'StandsAndAccess',
            'url' => '/manage-stands-access/list-stands-accesses',
            'menu_id' => 12,
            'index' => 1
        ]);

        DB::table('Menu_Children')->insert([
            'menu_children_id' => 31,
            'key' => 'PacksList',
            'url' => '/manage-packs/list-packs',
            'menu_id' => 13,
            'index' => 1
        ]);

        DB::table('Menu_Children')->insert([
            'menu_children_id' => 32,
            'key' => 'AddPack',
            'url' => '/manage-packs/add-pack',
            'menu_id' => 13,
            'index' => 2
        ]);

        DB::table('Menu_Children')->insert([
            'menu_children_id' => 33,
            'key' => 'SMSing',
            'url' => '/hors-event/manage-sms/history',
            'icon' => 'fa fa-sms',
            'menu_id' => 15,
            'index' => 1
        ]);

        DB::table('Menu_Children')->insert([
            'menu_children_id' => 34,
            'key' => 'CustomSMS',
            'url' => '/hors-event/manage-sms/custom',
            'icon' => 'icon-pencil3',
            'menu_id' => 15,
            'index' => 2
        ]);

        DB::table('Menu_Children')->insert([
            'menu_children_id' => 35,
            'key' => 'StartJoin',
            'url' => '/hors-event/manage-room/list-room',
            'icon' => 'icon-video-camera',
            'menu_id' => 16,
            'index' => 1
        ]);
    }
}

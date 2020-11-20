<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuSeedTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Menu')->insert([
            'menu_id' => 1,
            'key' => "ManageParticipants",
            'icon' => "icon-user"
        ]);

        DB::table('Menu')->insert([
            'menu_id' => 2,
            'key' => "ManageOrganizer",
            'icon' => "icon-user"
        ]);

        DB::table('Menu')->insert([
            'menu_id' => 3,
            'key' => "ManageBadgeAttestation",
            'icon' => "icon-download"
        ]);

        DB::table('Menu')->insert([
            'menu_id' => 4,
            'key' => 'ManageSubmission',
            'icon' => "icon-file-text2"
        ]);

        DB::table('Menu')->insert([
            'menu_id' => 5,
            'key' => 'ManageOrganism',
            'icon' => 'icon-office'
        ]);

        DB::table('Menu')->insert([
            'menu_id' => 6,
            'key' => 'Stats',
            'icon' => 'icon-stats-growth'
        ]);

        DB::table('Menu')->insert([
            'menu_id' => 7,
            'key' => 'SendMails',
            'icon' => 'icon-envelop'
        ]);

        DB::table('Menu')->insert([
            'menu_id' => 8,
            'key' => 'PostSondage',
            'icon' => 'icon-align-left'
        ]);

        DB::table('Menu')->insert([
            'menu_id' => 9,
            'key' => 'QuizAccess',
            'icon' => 'icon-graph'
        ]);

        DB::table('Menu')->insert([
            'menu_id' => 10,
            'key' => 'ManageProgram',
            'icon' => 'icon-graph'
        ]);

        DB::table('Menu')->insert([
            'menu_id' => 11,
            'key' => 'ManageStand',
            'icon' => 'icon-graph'
        ]);

        DB::table('Menu')->insert([
            'menu_id' => 12,
            'key' => 'Regie',
            'icon' => 'icon-wrench'
        ]);

        DB::table('Menu')->insert([
            'menu_id' => 13,
            'key' => 'ManagePacks',
            'icon' => 'icon-graph'
        ]);

        DB::table('Menu')->insert([
            'menu_id' => 14,
            'key' => 'EspaceHorsEvent',
            'icon' => 'icon-exit',
            'url' => '/hors-event',
            'reload' => true
        ]);

        DB::table('Menu')->insert([
            'menu_id' => 15,
            'key' => 'MarketingTools',
            'icon' => 'fa fa-comments',
            'showAfterReload' => 1
        ]);

        DB::table('Menu')->insert([
            'menu_id' => 16,
            'key' => 'MeetingTools',
            'icon' => 'icon-video-camera2',
            'showAfterReload' => 1
        ]);
    }
}

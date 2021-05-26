<?php

namespace Database\Seeders;
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
            'icon' => "icon-user",
            'index' => 1
        ]);

        DB::table('Menu')->insert([
            'menu_id' => 2,
            'key' => "ManageOrganizer",
            'icon' => "icon-user",
            'index' => 2
        ]);

        DB::table('Menu')->insert([
            'menu_id' => 3,
            'key' => "ManageBadgeAttestation",
            'icon' => "icon-download",
            'index' => 4
        ]);

        DB::table('Menu')->insert([
            'menu_id' => 4,
            'key' => 'ManageSubmission',
            'icon' => "icon-file-text2",
            'index' => 5
        ]);

        DB::table('Menu')->insert([
            'menu_id' => 5,
            'key' => 'ManageOrganism',
            'icon' => 'icon-office',
            'index' => 6
        ]);

        DB::table('Menu')->insert([
            'menu_id' => 6,
            'key' => 'Stats',
            'icon' => 'icon-stats-growth',
            'index' => 7
        ]);

        DB::table('Menu')->insert([
            'menu_id' => 7,
            'key' => 'SendMails',
            'icon' => 'icon-envelop',
            'index' => 8
        ]);

        DB::table('Menu')->insert([
            'menu_id' => 8,
            'key' => 'PostSondage',
            'icon' => 'icon-align-left',
            'index' => 9
        ]);

        DB::table('Menu')->insert([
            'menu_id' => 9,
            'key' => 'QuizAccess',
            'icon' => 'icon-graph',
            'index' => 10
        ]);

        DB::table('Menu')->insert([
            'menu_id' => 10,
            'key' => 'ManageProgram',
            'icon' => 'icon-graph',
            'index' => 11
        ]);

        DB::table('Menu')->insert([
            'menu_id' => 11,
            'key' => 'ManageStand',
            'icon' => 'icon-graph',
            'index' => 12
        ]);

        DB::table('Menu')->insert([
            'menu_id' => 12,
            'key' => 'Regie',
            'icon' => 'icon-wrench',
            'index' => 13
        ]);

        DB::table('Menu')->insert([
            'menu_id' => 13,
            'key' => 'ManagePacks',
            'icon' => 'icon-graph',
            'index' => 14
        ]);

        DB::table('Menu')->insert([
            'menu_id' => 14,
            'key' => 'EspaceHorsEvent',
            'icon' => 'icon-exit',
            'url' => '/hors-event',
            'reload' => true,
            'index' => 15
        ]);

        DB::table('Menu')->insert([
            'menu_id' => 15,
            'key' => 'MarketingTools',
            'icon' => 'fa fa-comments',
            'show_after_reload' => 1,
            'index' => 16
        ]);

        DB::table('Menu')->insert([
            'menu_id' => 16,
            'key' => 'MeetingTools',
            'icon' => 'icon-video-camera2',
            'show_after_reload' => 1,
            'index' => 17
        ]);

        
    }
}

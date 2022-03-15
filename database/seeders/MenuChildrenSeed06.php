<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class MenuChildrenSeed06 extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Menu_Children')->insert([
            'menu_children_id' => 44,
            'key' => 'configSubmission',
            'url' => '/configuration/:congress_id/config-Submission',
            'menu_id' => 18,
            'index' => 2
        ]);
        DB::table('Menu_Children')->insert([
            'menu_children_id' => 45,
            'key' => 'OnlineEvent',
            'url' => '/configuration/:congress_id/Online-Event',
            'menu_id' => 18,
            'index' => 3
        ]);
        DB::table('Menu_Children')->insert([
            'menu_children_id' => 46,
            'key' => 'Meetings',
            'url' => '/configuration/:congress_id/Meetings',
            'menu_id' => 18,
            'index' => 4
        ]);
        
    }
}

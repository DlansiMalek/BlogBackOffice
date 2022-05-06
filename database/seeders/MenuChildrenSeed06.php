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
            'menu_children_id' => 46,
            'key' => 'ConfigSubmission',
            'url' => '/manage-congress/:congress_id/config-submission',
            'menu_id' => 18,
            'index' => 2
        ]);
        DB::table('Menu_Children')->insert([
            'menu_children_id' => 47,
            'key' => 'OnlineEvent',
            'url' => '/manage-congress/:congress_id/online-event',
            'menu_id' => 18,
            'index' => 3
        ]);
        DB::table('Menu_Children')->insert([
            'menu_children_id' => 48,
            'key' => 'Meetings',
            'url' => '/manage-congress/:congress_id/meetings',
            'menu_id' => 18,
            'index' => 4
        ]);
    }
}

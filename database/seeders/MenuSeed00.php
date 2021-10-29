<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuSeed00 extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Menu')->insert([
            'menu_id' => 17,
            'key' => 'ManageLP',
            'icon' => 'icon-file-text2',
            'index' => 3
        ]);

        DB::table('Menu_Children')->insert([
            'menu_children_id' => 38,
            'key' => 'Configuration',
            'url' => '/manage-landing-page',
            'menu_id' => 17,
            'index' => 1
        ]);
    }
}

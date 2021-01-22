<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuChildSeed00 extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Menu_Children')->insert([
            'menu_children_id' => 36,
            'key' => 'ManageWhiteList',
            'url' => '/manage-participant/white-list',
            'menu_id' => 1
        ]);

        DB::table('Menu_Children')->insert([
            'menu_children_id' => 37,
            'key' => 'Tableaudebord',
            'url' => '/manage-mail/stats-mail',
            'menu_id' => 7
        ]);

    }
}

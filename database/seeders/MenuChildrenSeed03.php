<?php

namespace Database\Seeders;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class MenuChildrenSeed03 extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Menu_Children')->insert([
            'menu_children_id' => 41,
            'key' => 'ManageMenus',
            'url' => '/manage-menus',
            'menu_id' => 17,
            'index' => 2
        ]);
    }
}

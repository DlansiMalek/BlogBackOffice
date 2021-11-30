<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PrivilegeMenuChildrenTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Privilege_Menu_Children')->insert([
            'menu_children_id' => 15,
            'privilege_id' => 7,
            'menu_id' => 5,
        ]);
    }
}

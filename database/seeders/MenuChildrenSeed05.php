<?php

namespace Database\Seeders;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class MenuChildrenSeed05 extends Seeder
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
            'key' => 'Tables',
            'url' => '/stats/manage-meetings/list-meeting-tables',
            'menu_id' => 6,
            'index' => 1
        ]);
    }
}

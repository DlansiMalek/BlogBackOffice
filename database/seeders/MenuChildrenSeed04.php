<?php

namespace Database\Seeders;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class MenuChildrenSeed04 extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Menu_Children')->insert([
            'menu_children_id' => 43,
            'key' => 'WaitingRoom',
            'url' => '/configuration/page-attente',
            'menu_id' => 18,
            'index' => 1
        ]);

       
    }
}

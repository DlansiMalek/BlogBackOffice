<?php

namespace Database\Seeders;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class MenuChildrenSeed02 extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      DB::table('Menu_Children')->insert([
            'key' => 'Chat',
            'url' => '/manage-chat',
            'menu_id' => 10,
            'index' => 4
        ]);
    }
}

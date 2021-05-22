<?php

namespace Database\Seeders;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class MenuChildrenSeed01 extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Menu_Children')->insert([
            'menu_children_id' => 39,
            'key' => 'ConfigQuiz',
            'url' => '/manage-quiz/list',
            'menu_id' => 10,
            'index' => 3
        ]);
    }
}

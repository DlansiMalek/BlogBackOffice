<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ActionSeedTable001 extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Action')->insert([
            'key' => 'ENTRY_LEAVE',
            'value' => 'Entrée et sortie'
        ]);

        DB::table('Action')->insert([
            'key' => 'LOGIN_LOGOUT',
            'value' => 'Login et logout'
        ]);
    }
}

<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ActionSeedTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Action')->insert([
            'key' => 'login',
            'value' => 'Login'
        ]);

        DB::table('Action')->insert([
            'key' => 'logout',
            'value' => 'Logout'
        ]);

        DB::table('Action')->insert([
            'key' => 'entry',
            'value' => 'Entrée'
        ]);

        DB::table('Action')->insert([
            'key' => 'leave',
            'value' => 'Sortie'
        ]);

        DB::table('Action')->insert([
            'key' => 'download-pdf',
            'value' => 'Download pdf'
        ]);

        DB::table('Action')->insert([
            'key' => 'call-user',
            'value' => 'Call user'
        ]);

        DB::table('Action')->insert([
            'key' => 'game-played',
            'value' => 'Game Played'
        ]);
    }
}

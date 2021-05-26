<?php

namespace Database\Seeders;
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
            'key' => 'LOGIN',
            'value' => 'Login'
        ]);

        DB::table('Action')->insert([
            'key' => 'LOGOUT',
            'value' => 'Logout'
        ]);

        DB::table('Action')->insert([
            'key' => 'ENTRY',
            'value' => 'Entrée'
        ]);

        DB::table('Action')->insert([
            'key' => 'LEAVE',
            'value' => 'Sortie'
        ]);

        DB::table('Action')->insert([
            'key' => 'DOWNLOADPDF',
            'value' => 'Download pdf'
        ]);

        DB::table('Action')->insert([
            'key' => 'VIDEOCALL',
            'value' => 'Video call user'
        ]);

        DB::table('Action')->insert([
            'key' => 'VOICECALL',
            'value' => 'Voice call user'
        ]);

        DB::table('Action')->insert([
            'key' => 'VIDEOJOINED',
            'value' => 'Video joined call user'
        ]);

        DB::table('Action')->insert([
            'key' => 'VOICEJOINED',
            'value' => 'Voice joined call user'
        ]);

        DB::table('Action')->insert([
            'key' => 'GAMEPLAYED',
            'value' => 'Game Played'
        ]);

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

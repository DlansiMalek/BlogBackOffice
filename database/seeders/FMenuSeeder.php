<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('FMenu')->insert([
            'key' => 'Reception',
            'fr_label' => 'Réception',
            'en_label' => 'Réception',
            'is_visible' => '1',
            'rank' => '1',
        ]);
        DB::table('FMenu')->insert([
            'key' => 'Scene',
            'fr_label' => 'Scéne',
            'en_label' => '',
            'is_visible' => '1',
            'rank' => '2',
        ]);
        DB::table('FMenu')->insert([
            'key' => 'Session',
            'fr_label' => 'Session',
            'en_label' => 'Session',
            'is_visible' => '1',
            'rank' => '3',
        ]);
        DB::table('FMenu')->insert([
            'key' => 'Stands',
            'fr_label' => 'Stands',
            'en_label' => 'Stands',
            'is_visible' => '1',
            'rank' => '4',
        ]);
        DB::table('FMenu')->insert([
            'key' => 'Meetings',
            'fr_label' => 'Meetings',
            'en_label' => 'Meetings',
            'is_visible' => '1',
            'rank' => '5',
        ]);
        DB::table('FMenu')->insert([
            'key' => 'Travaux',
            'fr_label' => 'Abstracts',
            'en_label' => 'Abstracts',
            'is_visible' => '1',
            'rank' => '6',
        ]);
        DB::table('FMenu')->insert([
            'key' => 'Support',
            'fr_label' => 'Support',
            'en_label' => 'Support',
            'is_visible' => '1',
            'rank' => '7',
        ]);
    }
}

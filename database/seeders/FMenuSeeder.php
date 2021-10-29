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
            'en_label' => 'Reception',
            'is_visible' => '1',
            'rank' => '1',
            'url' => '',
            'logo' => 'fas fa-home',
        ]);
        DB::table('FMenu')->insert([
            'key' => 'Scene',
            'fr_label' => 'Scéne',
            'en_label' => 'Scene',
            'is_visible' => '1',
            'rank' => '2',
            'url' => 'event-room',
            'logo' => 'fas fa-video',
          ]);
        DB::table('FMenu')->insert([
            'key' => 'Session',
            'fr_label' => 'Session',
            'en_label' => 'Session',
            'is_visible' => '1',
            'rank' => '3',
            'url' => 'access',
            'logo' => 'fas fa-users',
        ]);
        DB::table('FMenu')->insert([
            'key' => 'Stands',
            'fr_label' => 'Stands',
            'en_label' => 'Stands',
            'is_visible' => '1',
            'rank' => '4',
            'url' => 'stands',
            'logo' => 'fas fa-store',
         ]);
        DB::table('FMenu')->insert([
            'key' => 'Meetings',
            'fr_label' => 'Meetings',
            'en_label' => 'Meetings',
            'is_visible' => '1',
            'rank' => '5',
            'url' => 'meetings',
            'logo' => 'fas fa-store',
          ]);
        DB::table('FMenu')->insert([
            'key' => 'Travaux',
            'fr_label' => 'Travaux',
            'en_label' => 'Abstracts',
            'is_visible' => '1',
            'rank' => '6',
            'url' => 'submission',
            'logo' => 'fas fa-users',
        ]);
        DB::table('FMenu')->insert([
            'key' => 'Support',
            'fr_label' => 'Support',
            'en_label' => 'Support',
            'is_visible' => '1',
            'rank' => '7',
            'url' => 'support',
             'logo' => 'fas fa-magic',
      ]);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StandContentConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Stand_Content_Config')->insert([
            'stand_content_config_id'=> 1,
            'key' => 'logo_blanc',
            'name'=> 'LogoPngBlanc',
            'size '=> '',
            'default_file ' => '',
            'default_url ' => null,
            'accept_file' => 'jpg;png;jpeg',
            'stand_type_id' => 1
        ]);
        DB::table('Stand_Content_Config')->insert([
            'stand_content_config_id'=> 2,
            'key' => 'logo_rempli',
            'name'=> 'LogoPngRempli',
            'size '=> '',
            'default_file ' => '',
            'default_url ' => null,
            'accept_file' => 'jpg;png;jpeg',
            'stand_type_id' => 1
        ]);
        DB::table('Stand_Content_Config')->insert([
            'stand_content_config_id'=> 3,
            'key' => 'banniere_verticale',
            'name'=> 'BanniereVerticale',
            'size '=> '',
            'default_file ' => '',
            'default_url ' => null,
            'accept_file' => 'jpg;png;jpeg',
            'stand_type_id' => 1
        ]);
        DB::table('Stand_Content_Config')->insert([
            'stand_content_config_id'=> 4,
            'key' => 'banniere_carre',
            'name'=> 'BanniereCarre',
            'size '=> '',
            'default_file ' => '',
            'default_url ' => null,
            'accept_file' => 'jpg;png;jpeg',
            'stand_type_id' => 1
        ]);
        DB::table('Stand_Content_Config')->insert([
            'stand_content_config_id'=> 5,
            'key' => 'affiche_verticale_1',
            'name'=> 'AfficheVerticale1',
            'size '=> '',
            'default_file ' => '',
            'default_url ' => null,
            'accept_file' => 'jpg;png;jpeg',
            'stand_type_id' => 1
        ]);
        DB::table('Stand_Content_Config')->insert([
            'stand_content_config_id'=> 6,
            'key' => 'affiche_verticale_2',
            'name'=> 'AfficheVerticale2',
            'size '=> '',
            'default_file ' => '',
            'default_url ' => null,
            'accept_file' => 'jpg;png;jpeg',
            'stand_type_id' => 1
        ]);
        DB::table('Stand_Content_Config')->insert([
            'stand_content_config_id'=> 7,
            'key' => 'video',
            'name'=> 'Video',
            'size '=> '',
            'default_file ' => '',
            'default_url ' => null,
            'accept_file' => 'video/*',
            'stand_type_id' => 1
        ]);


        DB::table('Stand_Content_Config')->insert([
            'stand_content_config_id'=> 8,
            'key' => 'logo_blanc',
            'name'=> 'LogoPngBlanc',
            'size '=> '',
            'default_file ' => '',
            'default_url ' => null,
            'accept_file' => 'jpg;png;jpeg',
            'stand_type_id' => 2
        ]);
        DB::table('Stand_Content_Config')->insert([
            'stand_content_config_id'=> 9,
            'key' => 'logo_rempli',
            'name'=> 'LogoPngRempli',
            'size '=> '',
            'default_file ' => '',
            'default_url ' => null,
            'accept_file' => 'jpg;png;jpeg',
            'stand_type_id' => 2
        ]);
        DB::table('Stand_Content_Config')->insert([
            'stand_content_config_id'=> 10,
            'key' => 'banniere_verticale1',
            'name'=> 'BanniereVerticale1',
            'size '=> '',
            'default_file ' => '',
            'default_url ' => null,
            'accept_file' => 'jpg;png;jpeg',
            'stand_type_id' => 2
        ]);
        DB::table('Stand_Content_Config')->insert([
            'stand_content_config_id'=> 11,
            'key' => 'banniere_verticale2',
            'name'=> 'BanniereVerticale2',
            'size '=> '',
            'default_file ' => '',
            'default_url ' => null,
            'accept_file' => 'jpg;png;jpeg',
            'stand_type_id' => 2
        ]);
        DB::table('Stand_Content_Config')->insert([
            'stand_content_config_id'=> 12,
            'key' => 'banniere_carre_droite',
            'name'=> 'BanniereCarreDroite',
            'size '=> '',
            'default_file ' => '',
            'default_url ' => null,
            'accept_file' => 'jpg;png;jpeg',
            'stand_type_id' => 2
        ]);
        DB::table('Stand_Content_Config')->insert([
            'stand_content_config_id'=> 13,
            'key' => 'banniere_carre_gauche',
            'name'=> 'BanniereCarreGauche',
            'size '=> '',
            'default_file ' => '',
            'default_url ' => null,
            'accept_file' => 'jpg;png;jpeg',
            'stand_type_id' => 2
        ]);
        DB::table('Stand_Content_Config')->insert([
            'stand_content_config_id'=> 14,
            'key' => 'affiche_verticale_1',
            'name'=> 'AfficheVerticale1',
            'size '=> '',
            'default_file ' => '',
            'default_url ' => null,
            'accept_file' => 'jpg;png;jpeg',
            'stand_type_id' => 2
        ]);
        DB::table('Stand_Content_Config')->insert([
            'stand_content_config_id'=> 13,
            'key' => 'affiche_verticale_2',
            'name'=> 'AfficheVerticale2',
            'size '=> '',
            'default_file ' => '',
            'default_url ' => null,
            'accept_file' => 'jpg;png;jpeg',
            'stand_type_id' => 2
        ]);
        DB::table('Stand_Content_Config')->insert([
            'stand_content_config_id'=> 14,
            'key' => 'video',
            'name'=> 'Video',
            'size '=> '',
            'default_file ' => '',
            'default_url ' => null,
            'accept_file' => 'video/*',
            'stand_type_id' => 2
        ]);
    }
}

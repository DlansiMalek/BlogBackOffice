<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConfigLPSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Config_LP')->insert([
            'congress_id' => 1,
            'header_logo_event' => 'bRZfBIQiWcDEkbp3iiBThJWcxUm3QukqrVDgVxdp.jpg',
            'is_inscription' => 1,
            'home_banner_event' => 'bRZfBIQiWcDEkbp3iiBThJWcxUm3QukqrVDgVxdp.jpg',
            'home_start_date' => date("Y-m-d"),
            'home_end_date' => date("Y-m-d"),
            'home_description' => 'a small description',
            'prp_title' => 'About title',
            'prp_description' => 'About description',
            'prp_banner_event' =>  'bRZfBIQiWcDEkbp3iiBThJWcxUm3QukqrVDgVxdp.jpg',
            'speaker_title' => 'Speaker test',
            'speaker_description' => 'Speaker description',
            'sponsor_title' => 'Sponsors title',
            'sponsor_description' => 'Sponsors description',
            'prg_title' => 'Program title',
            'prg_description' => 'Program description',
            'contact_title' => 'Contact title',
            'contact_description' => 'Contact description',
            'theme_color' => 'default',
            'theme_mode' => 'style'
        ]);

        DB::table('Config_LP')->insert([
            'congress_id' => 2,
            'header_logo_event' => 'bRZfBIQiWcDEkbp3iiBThJWcxUm3QukqrVDgVxdp.jpg',
            'is_inscription' => 0,
            'home_banner_event' => 'bRZfBIQiWcDEkbp3iiBThJWcxUm3QukqrVDgVxdp.jpg',
            'home_start_date' => date("Y-m-d"),
            'home_end_date' => date("Y-m-d"),
            'home_description' => 'a small description',
            'prp_title' => 'About title',
            'prp_description' => 'About description',
            'prp_banner_event' =>  'bRZfBIQiWcDEkbp3iiBThJWcxUm3QukqrVDgVxdp.jpg',
            'speaker_title' => 'Speaker test',
            'speaker_description' => 'Speaker description',
            'sponsor_title' => 'Sponsors title',
            'sponsor_description' => 'Sponsors description',
            'prg_title' => 'Program title',
            'prg_description' => 'Program description',
            'contact_title' => 'Contact title',
            'contact_description' => 'Contact description',
            'theme_color' => 'default',
            'theme_mode' => 'style'
        ]);
        
        DB::table('Config_LP')->insert([
            'congress_id' => 3,
            'header_logo_event' => 'bRZfBIQiWcDEkbp3iiBThJWcxUm3QukqrVDgVxdp.jpg',
            'is_inscription' => 1,
            'home_banner_event' => 'bRZfBIQiWcDEkbp3iiBThJWcxUm3QukqrVDgVxdp.jpg',
            'home_start_date' => date("Y-m-d"),
            'home_end_date' => date("Y-m-d"),
            'home_description' => 'a small description',
            'prp_title' => 'About title',
            'prp_description' => 'About description',
            'prp_banner_event' =>  'bRZfBIQiWcDEkbp3iiBThJWcxUm3QukqrVDgVxdp.jpg',
            'speaker_title' => 'Speaker test',
            'speaker_description' => 'Speaker description',
            'sponsor_title' => 'Sponsors title',
            'sponsor_description' => 'Sponsors description',
            'prg_title' => 'Program title',
            'prg_description' => 'Program description',
            'contact_title' => 'Contact title',
            'contact_description' => 'Contact description',
            'theme_color' => 'default',
            'theme_mode' => 'style'
        ]);
    }
}

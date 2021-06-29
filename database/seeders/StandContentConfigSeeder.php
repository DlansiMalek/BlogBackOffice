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
            'label'=> 'LogoPngBlanc',
            'size'=> null,
            'default_file' => null,
            'default_url' => null,
            'accept_file' => 'jpg;png;jpeg',
            'stand_type_id' => 1
        ]);
        DB::table('Stand_Content_Config')->insert([
            'stand_content_config_id'=> 2,
            'key' => 'logo_rempli',
            'label'=> 'LogoPngRempli',
            'size'=> null,
            'default_file' => null,
            'default_url' => null,
            'accept_file' => 'jpg;png;jpeg',
            'stand_type_id' => 1
        ]);
        DB::table('Stand_Content_Config')->insert([
            'stand_content_config_id'=> 3,
            'key' => 'banniere_verticale',
            'label'=> 'BanniereVerticale',
            'size'=> null,
            'default_file' => null,
            'default_url' => null,
            'accept_file' => 'jpg;png;jpeg',
            'stand_type_id' => 1
        ]);
        DB::table('Stand_Content_Config')->insert([
            'stand_content_config_id'=> 4,
            'key' => 'banniere_carre',
            'label'=> 'BanniereCarre',
            'size'=> null,
            'default_file' => null,
            'default_url' => null,
            'accept_file' => 'jpg;png;jpeg',
            'stand_type_id' => 1
        ]);
        DB::table('Stand_Content_Config')->insert([
            'stand_content_config_id'=> 5,
            'key' => 'affiche_verticale_1',
            'label'=> 'AfficheVerticale1',
            'size'=> null,
            'default_file' => null,
            'default_url' => null,
            'accept_file' => 'jpg;png;jpeg',
            'stand_type_id' => 1
        ]);
        DB::table('Stand_Content_Config')->insert([
            'stand_content_config_id'=> 6,
            'key' => 'affiche_verticale_2',
            'label'=> 'AfficheVerticale2',
            'size'=> null,
            'default_file' => null,
            'default_url' => null,
            'accept_file' => 'jpg;png;jpeg',
            'stand_type_id' => 1
        ]);
        DB::table('Stand_Content_Config')->insert([
            'stand_content_config_id'=> 7,
            'key' => 'video',
            'label'=> 'Video',
            'size'=> null,
            'default_file' => null,
            'default_url' => null,
            'accept_file' => 'video/*',
            'stand_type_id' => 1
        ]);



        DB::table('Stand_Content_Config')->insert([
            'stand_content_config_id'=> 8,
            'key' => 'logo_blanc',
            'label'=> 'LogoPngBlanc',
            'size'=> null,
            'default_file' => null,
            'default_url' => null,
            'accept_file' => 'jpg;png;jpeg',
            'stand_type_id' => 2
        ]);
        DB::table('Stand_Content_Config')->insert([
            'stand_content_config_id'=> 9,
            'key' => 'logo_rempli',
            'label'=> 'LogoPngRempli',
            'size'=> null,
            'default_file' => null,
            'default_url' => null,
            'accept_file' => 'jpg;png;jpeg',
            'stand_type_id' => 2
        ]);
        DB::table('Stand_Content_Config')->insert([
            'stand_content_config_id'=> 10,
            'key' => 'banniere_verticale1',
            'label'=> 'BanniereVerticale1',
            'size'=> null,
            'default_file' => null,
            'default_url' => null,
            'accept_file' => 'jpg;png;jpeg',
            'stand_type_id' => 2
        ]);
        DB::table('Stand_Content_Config')->insert([
            'stand_content_config_id'=> 11,
            'key' => 'banniere_verticale2',
            'label'=> 'BanniereVerticale2',
            'size'=> null,
            'default_file' => null,
            'default_url' => null,
            'accept_file' => 'jpg;png;jpeg',
            'stand_type_id' => 2
        ]);
        DB::table('Stand_Content_Config')->insert([
            'stand_content_config_id'=> 12,
            'key' => 'banniere_carre_droite',
            'label'=> 'BanniereCarreDroite',
            'size'=> null,
            'default_file' => null,
            'default_url' => null,
            'accept_file' => 'jpg;png;jpeg',
            'stand_type_id' => 2
        ]);
        DB::table('Stand_Content_Config')->insert([
            'stand_content_config_id'=> 13,
            'key' => 'banniere_carre_gauche',
            'label'=> 'BanniereCarreGauche',
            'size'=> null,
            'default_file' => null,
            'default_url' => null,
            'accept_file' => 'jpg;png;jpeg',
            'stand_type_id' => 2
        ]);
        DB::table('Stand_Content_Config')->insert([
            'stand_content_config_id'=> 14,
            'key' => 'affiche_verticale_1',
            'label'=> 'AfficheVerticale1',
            'size'=> null,
            'default_file' => null,
            'default_url' => null,
            'accept_file' => 'jpg;png;jpeg',
            'stand_type_id' => 2
        ]);
        DB::table('Stand_Content_Config')->insert([
            'stand_content_config_id'=> 15,
            'key' => 'affiche_verticale_2',
            'label'=> 'AfficheVerticale2',
            'size'=> null,
            'default_file' => null,
            'default_url' => null,
            'accept_file' => 'jpg;png;jpeg',
            'stand_type_id' => 2
        ]);
        DB::table('Stand_Content_Config')->insert([
            'stand_content_config_id'=> 16,
            'key' => 'affiche_verticale_3',
            'label'=> 'AfficheVerticale3',
            'size'=> null,
            'default_file' => null,
            'default_url' => null,
            'accept_file' => 'jpg;png;jpeg',
            'stand_type_id' => 2
        ]);
        DB::table('Stand_Content_Config')->insert([
            'stand_content_config_id'=> 17,
            'key' => 'affiche_verticale_4',
            'label'=> 'AfficheVerticale4',
            'size'=> null,
            'default_file' => null,
            'default_url' => null,
            'accept_file' => 'jpg;png;jpeg',
            'stand_type_id' => 2
        ]);
        DB::table('Stand_Content_Config')->insert([
            'stand_content_config_id'=> 18,
            'key' => 'video',
            'label'=> 'Video',
            'size'=> null,
            'default_file' => null,
            'default_url' => null,
            'accept_file' => 'video/*',
            'stand_type_id' => 2
        ]);

        

        DB::table('Stand_Content_Config')->insert([
            'stand_content_config_id'=> 19,
            'key' => 'logo_blanc',
            'label'=> 'LogoPngBlanc',
            'size'=> null,
            'default_file' => null,
            'default_url' => null,
            'accept_file' => 'jpg;png;jpeg',
            'stand_type_id' => 3
        ]);
        DB::table('Stand_Content_Config')->insert([
            'stand_content_config_id'=> 20,
            'key' => 'logo_rempli',
            'label'=> 'LogoPngRempli',
            'size'=> null,
            'default_file' => null,
            'default_url' => null,
            'accept_file' => 'jpg;png;jpeg',
            'stand_type_id' => 3
        ]);
        DB::table('Stand_Content_Config')->insert([
            'stand_content_config_id'=> 21,
            'key' => 'banniere_verticale1',
            'label'=> 'BanniereVerticale1',
            'size'=> null,
            'default_file' => null,
            'default_url' => null,
            'accept_file' => 'jpg;png;jpeg',
            'stand_type_id' => 3
        ]);
        DB::table('Stand_Content_Config')->insert([
            'stand_content_config_id'=> 22,
            'key' => 'banniere_verticale2',
            'label'=> 'BanniereVerticale2',
            'size'=> null,
            'default_file' => null,
            'default_url' => null,
            'accept_file' => 'jpg;png;jpeg',
            'stand_type_id' => 3
        ]);
        DB::table('Stand_Content_Config')->insert([
            'stand_content_config_id'=> 23,
            'key' => 'banniere_verticale3',
            'label'=> 'BanniereVerticale3',
            'size'=> null,
            'default_file' => null,
            'default_url' => null,
            'accept_file' => 'jpg;png;jpeg',
            'stand_type_id' => 3
        ]);
        DB::table('Stand_Content_Config')->insert([
            'stand_content_config_id'=> 24,
            'key' => 'banniere_verticale4',
            'label'=> 'BanniereVerticale4',
            'size'=> null,
            'default_file' => null,
            'default_url' => null,
            'accept_file' => 'jpg;png;jpeg',
            'stand_type_id' => 3
        ]);
        DB::table('Stand_Content_Config')->insert([
            'stand_content_config_id'=> 25,
            'key' => 'affiche_carre1',
            'label'=> 'AfficheCarre1',
            'size'=> null,
            'default_file' => null,
            'default_url' => null,
            'accept_file' => 'jpg;png;jpeg',
            'stand_type_id' => 3
        ]);
        DB::table('Stand_Content_Config')->insert([
            'stand_content_config_id'=> 26,
            'key' => 'affiche_carre2',
            'label'=> 'AfficheCarre2',
            'size'=> null,
            'default_file' => null,
            'default_url' => null,
            'accept_file' => 'jpg;png;jpeg',
            'stand_type_id' => 3
        ]);
        DB::table('Stand_Content_Config')->insert([
            'stand_content_config_id'=> 27,
            'key' => 'affiche_carre3',
            'label'=> 'AfficheCarre3',
            'size'=> null,
            'default_file' => null,
            'default_url' => null,
            'accept_file' => 'jpg;png;jpeg',
            'stand_type_id' => 3
        ]);
        DB::table('Stand_Content_Config')->insert([
            'stand_content_config_id'=> 28,
            'key' => 'banniere_carre_droite_table1',
            'label'=> 'BanniereCarreDroiteTable1',
            'size'=> null,
            'default_file' => null,
            'default_url' => null,
            'accept_file' => 'jpg;png;jpeg',
            'stand_type_id' => 3
        ]);
        DB::table('Stand_Content_Config')->insert([
            'stand_content_config_id'=> 29,
            'key' => 'banniere_carre_gauche_table1',
            'label'=> 'BanniereCarreGaucheTable1',
            'size'=> null,
            'default_file' => null,
            'default_url' => null,
            'accept_file' => 'jpg;png;jpeg',
            'stand_type_id' => 3
        ]);
        DB::table('Stand_Content_Config')->insert([
            'stand_content_config_id'=> 30,
            'key' => 'banniere_carre_droite_table2',
            'label'=> 'BanniereCarreDroiteTable2',
            'size'=> null,
            'default_file' => null,
            'default_url' => null,
            'accept_file' => 'jpg;png;jpeg',
            'stand_type_id' => 3
        ]);
        DB::table('Stand_Content_Config')->insert([
            'stand_content_config_id'=> 31,
            'key' => 'banniere_carre_gauche_table2',
            'label'=> 'BanniereCarreGaucheTable2',
            'size'=> null,
            'default_file' => null,
            'default_url' => null,
            'accept_file' => 'jpg;png;jpeg',
            'stand_type_id' => 3
        ]);
        DB::table('Stand_Content_Config')->insert([
            'stand_content_config_id'=> 32,
            'key' => 'affiche_verticale_1_droite',
            'label'=> 'AfficheVerticale1Droite',
            'size'=> null,
            'default_file' => null,
            'default_url' => null,
            'accept_file' => 'jpg;png;jpeg',
            'stand_type_id' => 3
        ]);
        DB::table('Stand_Content_Config')->insert([
            'stand_content_config_id'=> 33,
            'key' => 'affiche_verticale_2_droite',
            'label'=> 'AfficheVerticale2Droite',
            'size'=> null,
            'default_file' => null,
            'default_url' => null,
            'accept_file' => 'jpg;png;jpeg',
            'stand_type_id' => 3
        ]);
        DB::table('Stand_Content_Config')->insert([
            'stand_content_config_id'=> 34,
            'key' => 'affiche_verticale_3_gauche',
            'label'=> 'AfficheVerticale3Gauche',
            'size'=> null,
            'default_file' => null,
            'default_url' => null,
            'accept_file' => 'jpg;png;jpeg',
            'stand_type_id' => 3
        ]);
        DB::table('Stand_Content_Config')->insert([
            'stand_content_config_id'=> 35,
            'key' => 'affiche_verticale_4_gauche',
            'label'=> 'AfficheVerticale4Gauche',
            'size'=> null,
            'default_file' => null,
            'default_url' => null,
            'accept_file' => 'jpg;png;jpeg',
            'stand_type_id' => 3
        ]);
        DB::table('Stand_Content_Config')->insert([
            'stand_content_config_id'=> 36,
            'key' => 'video',
            'label'=> 'Video',
            'size'=> null,
            'default_file' => null,
            'default_url' => null,
            'accept_file' => 'video/*',
            'stand_type_id' => 3
        ]);



        DB::table('Stand_Content_Config')->insert([
            'stand_content_config_id'=> 37,
            'key' => 'logo_colore',
            'label'=> 'LogoPngColore',
            'size'=> null,
            'default_file' => null,
            'default_url' => null,
            'accept_file' => 'jpg;png;jpeg',
            'stand_type_id' => 4
        ]);
        DB::table('Stand_Content_Config')->insert([
            'stand_content_config_id'=> 38,
            'key' => 'video_tv',
            'label'=> 'VideoTV',
            'size'=> null,
            'default_file' => null,
            'default_url' => null,
            'accept_file' => 'video/*',
            'stand_type_id' => 4
        ]);
        DB::table('Stand_Content_Config')->insert([
            'stand_content_config_id'=> 39,
            'key' => 'video_table_1',
            'label'=> 'VideoTable1',
            'size'=> null,
            'default_file' => null,
            'default_url' => null,
            'accept_file' => 'video/*',
            'stand_type_id' => 4
        ]);
        DB::table('Stand_Content_Config')->insert([
            'stand_content_config_id'=> 40,
            'key' => 'video_table_2',
            'label'=> 'VideoTable2',
            'size'=> null,
            'default_file' => null,
            'default_url' => null,
            'accept_file' => 'video/*',
            'stand_type_id' => 4
        ]);
        DB::table('Stand_Content_Config')->insert([
            'stand_content_config_id'=> 41,
            'key' => 'banniere_carre_table_1',
            'label'=> 'BanniereCarreTable1',
            'size'=> null,
            'default_file' => null,
            'default_url' => null,
            'accept_file' => 'jpg;png;jpeg',
            'stand_type_id' => 4
        ]);
        DB::table('Stand_Content_Config')->insert([
            'stand_content_config_id'=> 42,
            'key' => 'banniere_carre_table_2',
            'label'=> 'BanniereCarreTable2',
            'size'=> null,
            'default_file' => null,
            'default_url' => null,
            'accept_file' => 'jpg;png;jpeg',
            'stand_type_id' => 4
        ]);
        DB::table('Stand_Content_Config')->insert([
            'stand_content_config_id'=> 43,
            'key' => 'banniere_verticale',
            'label'=> 'BanniereVerticale',
            'size'=> null,
            'default_file' => null,
            'default_url' => null,
            'accept_file' => 'jpg;png;jpeg',
            'stand_type_id' => 4
        ]);



        DB::table('Stand_Content_Config')->insert([
            'stand_content_config_id'=> 44,
            'key' => 'logo',
            'label'=> 'LogoPng',
            'size'=> null,
            'default_file' => null,
            'default_url' => null,
            'accept_file' => 'jpg;png;jpeg',
            'stand_type_id' => 5
        ]);
        DB::table('Stand_Content_Config')->insert([
            'stand_content_config_id'=> 45,
            'key' => 'affiche_carre1',
            'label'=> 'AfficheCarre1',
            'size'=> null,
            'default_file' => null,
            'default_url' => null,
            'accept_file' => 'jpg;png;jpeg',
            'stand_type_id' => 5
        ]);
        DB::table('Stand_Content_Config')->insert([
            'stand_content_config_id'=> 46,
            'key' => 'affiche_carre2',
            'label'=> 'AfficheCarre2',
            'size'=> null,
            'default_file' => null,
            'default_url' => null,
            'accept_file' => 'jpg;png;jpeg',
            'stand_type_id' => 5
        ]);
        DB::table('Stand_Content_Config')->insert([
            'stand_content_config_id'=> 47,
            'key' => 'affiche_carre3',
            'label'=> 'AfficheCarre3',
            'size'=> null,
            'default_file' => null,
            'default_url' => null,
            'accept_file' => 'jpg;png;jpeg',
            'stand_type_id' => 5
        ]);
        DB::table('Stand_Content_Config')->insert([
            'stand_content_config_id'=> 48,
            'key' => 'affiche_carre4',
            'label'=> 'AfficheCarre4',
            'size'=> null,
            'default_file' => null,
            'default_url' => null,
            'accept_file' => 'jpg;png;jpeg',
            'stand_type_id' => 5
        ]);
        DB::table('Stand_Content_Config')->insert([
            'stand_content_config_id'=> 49,
            'key' => 'affiche_carre5',
            'label'=> 'AfficheCarre5',
            'size'=> null,
            'default_file' => null,
            'default_url' => null,
            'accept_file' => 'jpg;png;jpeg',
            'stand_type_id' => 5
        ]);
        DB::table('Stand_Content_Config')->insert([
            'stand_content_config_id'=> 50,
            'key' => 'affiche_carre6',
            'label'=> 'AfficheCarre6',
            'size'=> null,
            'default_file' => null,
            'default_url' => null,
            'accept_file' => 'jpg;png;jpeg',
            'stand_type_id' => 5
        ]);
        DB::table('Stand_Content_Config')->insert([
            'stand_content_config_id'=> 51,
            'key' => 'video_tv1',
            'label'=> 'VideoTV1',
            'size'=> null,
            'default_file' => null,
            'default_url' => null,
            'accept_file' => 'video/*',
            'stand_type_id' => 5
        ]);
        DB::table('Stand_Content_Config')->insert([
            'stand_content_config_id'=> 52,
            'key' => 'video_tv2',
            'label'=> 'VideoTV2',
            'size'=> null,
            'default_file' => null,
            'default_url' => null,
            'accept_file' => 'video/*',
            'stand_type_id' => 5
        ]);
        DB::table('Stand_Content_Config')->insert([
            'stand_content_config_id'=> 53,
            'key' => 'video_tv3',
            'label'=> 'VideoTV3',
            'size'=> null,
            'default_file' => null,
            'default_url' => null,
            'accept_file' => 'video/*',
            'stand_type_id' => 5
        ]);

    }
}

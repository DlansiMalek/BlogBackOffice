<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateFmenuContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      
        Db::table('FMenu')->where('rank', '=', '7')->update(['rank'=>'8']);

        DB::table('FMenu')->insert([
            'key' => 'Tables',
            'fr_label' => 'Tables',
            'en_label' => 'Tables',
            'is_visible' => '1',
            'rank' => '7',
            'url' => 'tables',
            'logo' => 'fas fa-handshake',
        ]);      
    }
}

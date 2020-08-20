<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ThemeAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Theme_Admin')->insert([
            'theme_id'=>'1',
            'admin_id'=>'1'
        ]);
        DB::table('Theme_Admin')->insert([
            'theme_id'=>'2',
            'admin_id'=>'2'
        ]);
    }
}

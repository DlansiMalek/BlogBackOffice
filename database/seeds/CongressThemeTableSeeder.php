<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CongressThemeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Congress_Theme')->insert([
            'congress_id' => 1,
            'theme_id' => 1
        ]);
    }
}

<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class submission_themeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::insert('submission_theme')->insert([
            'congress_id'=>1,
            'theme_id'=>1
        ]);
    }
}

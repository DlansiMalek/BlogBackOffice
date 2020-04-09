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
        DB::insert('Submission_Theme')->insert([
            'congress_id'=>1,
            'theme_id'=>1
        ]);
    }
}

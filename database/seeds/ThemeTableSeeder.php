<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class ThemeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Theme')->insert([
            'label'=>'Science',
            'description'=>'Scientifique'
        ]);
        DB::table('Theme')->insert([
            'label'=>'Sport',
            'description'=>'Sport'
        ]);
        DB::table('Theme')->insert([
            'label'=>'Litterature',
            'description'=>'Litterature'
        ]);
    }
}

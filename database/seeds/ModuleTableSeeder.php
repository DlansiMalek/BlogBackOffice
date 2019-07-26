<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class ModuleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        
          //
          DB::table('module')->insert([
            'type'=>'Participants'
        ]);
        DB::table('module')->insert([
            'type'=>'Badge'
        ]);
        DB::table('module')->insert([
            'type'=>'Organization'
        ]);
    }
}

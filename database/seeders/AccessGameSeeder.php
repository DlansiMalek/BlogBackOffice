<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccessGameSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Access_Game')->insert([
            'user_id' => 1,
            'access_id' => 10,
            'score' => 40,
        ]);

        DB::table('Access_Game')->insert([
            'user_id' => 1,
            'access_id' => 10,
            'score' => 20,
        ]);
        
        DB::table('Access_Game')->insert([
            'user_id' => 1,
            'access_id' => 10,
            'score' => 100,
        ]);
    }
}

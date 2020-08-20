<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccessPresenceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Access_Presence')->insert([
            'user_id' => 1,
            'access_id' => 1,
            'entered_at' => date("Y-m-d"),
            'left_at' => date("Y-m-d")
        ]);
        DB::table('Access_Presence')->insert([
            'user_id' => 2,
            'access_id' => 6,
            'entered_at' => date("Y-m-d"),
            'left_at' => date("Y-m-d")
        ]);
        DB::table('Access_Presence')->insert([
            'user_id' => 3,
            'access_id' => 4,
            'entered_at' => date("Y-m-d"),
            'left_at' => date("Y-m-d")
        ]);
    }
}

<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccessChairSeedTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Access_Chair')->insert([
            'user_id' => 1,
            'access_id' => 1
        ]);
        DB::table('Access_Chair')->insert([
            'user_id' => 2,
            'access_id' => 2
        ]);
        DB::table('Access_Chair')->insert([
            'user_id' => 3,
            'access_id' => 3
        ]);
        DB::table('Access_Chair')->insert([
            'user_id' => 4,
            'access_id' => 4
        ]);
    }
}

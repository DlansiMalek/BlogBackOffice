<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserAccessSeedTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('User_Access')->insert([
            'user_id' => 1,
            'access_id' => 1,
            'isPresent' => 0
        ]);
        DB::table('User_Access')->insert([
            'user_id' => 2,
            'access_id' => 2,
            'isPresent' => 1
        ]);
        DB::table('User_Access')->insert([
            'user_id' => 3,
            'access_id' => 3,
            'isPresent' => 1
        ]);
        DB::table('User_Access')->insert([
            'user_id' => 4,
            'access_id' => 4,
            'isPresent' => 0
        ]);
    }
}

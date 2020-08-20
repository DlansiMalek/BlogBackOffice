<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserPackSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('User_Pack')->insert([
            'user_id' => 1,
            'pack_id' => 1
        ]);
        DB::table('User_Pack')->insert([
            'user_id' => 2,
            'pack_id' => 2
        ]);
        DB::table('User_Pack')->insert([
            'user_id' => 3,
            'pack_id' => 3
        ]);
    }
}

<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserCongressSeedTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('User_Congress')->insert([
            'user_id' => 1,
            'privilege_id' => 3,
            'congress_id' => 1
        ]);

        DB::table('User_Congress')->insert([
            'user_id' => 2,
            'privilege_id' => 5,
            'congress_id' => 1
        ]);
        DB::table('User_Congress')->insert([
            'user_id' => 3,
            'privilege_id' => 8,
            'congress_id' => 1
        ]);
    }
}

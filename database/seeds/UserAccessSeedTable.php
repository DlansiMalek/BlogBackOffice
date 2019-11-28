<?php

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
            'access_id' => 1
        ]);
    }
}

<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserMailAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('User_Mail_Admin')->insert([
            'user_id' => 1,
            'mail_admin_id' => 1,
            'status' => 0
        ]);
        DB::table('User_Mail_Admin')->insert([
            'user_id' => 2,
            'mail_admin_id' => 2,
            'status' => 1
        ]);
    }
}

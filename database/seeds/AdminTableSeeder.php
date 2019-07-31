<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Admin')->insert([
            'name' => 'Admin',
            'email' => 'admin@vayetek.com',
            'mobile' => '77777777',
            'privilege_id' => 1,
            'passwordDecrypt' => 'AdminVayetek',
            'password' => bcrypt('AdminVayetek')
        ]);
    }
}

<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminCongressTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Admin_Congress')->insert([
            'admin_id' => 1,
            'congress_id' => 1,
            'privilege_id' => 1
        ]);
    }
}

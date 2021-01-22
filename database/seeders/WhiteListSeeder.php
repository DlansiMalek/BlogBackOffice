<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WhiteListSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('White_List')->insert([
            'white_list_id' => 1,
            'first_name' => 'User',
            'last_name' => 'WhiteList',
            'mobile' => '777777',
            'email' => 'user_whitelist@vayetek.com'
        ]);
    }
}

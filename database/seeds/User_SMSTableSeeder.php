<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class User_SMSTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('User_SMS')->insert([
            'user_id'=>'1',
            'custom_sms_id'=>'1',
             'status'=>0,
        ]);
    }
}

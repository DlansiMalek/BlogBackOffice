<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('User')->insert([
            'first_name' => 'Vayetek',
            'last_name' => 'Team',
            'email' => 'contact@vayetek.com',
            'country_id' => 215,
            'email_verified' => 1,
            'verification_code' => str_random(30)
        ]);
    }
}

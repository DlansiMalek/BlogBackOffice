<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeedTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('User')->insert([
            'user_id' => 1,
            'first_name' => 'User',
            'last_name' => 'Vayetek',
            'gender' => 1,
            'mobile' => '777777',
            'email' => 'user@vayetek.com',
            'email_verified' => 1,
            'qr_code' => '7sqafkqm1',
            'country_id' => 'TUN',
            'password' => '$2y$10$6wz7ke5g9JSLubP/aw0WKu4KTV/uhc8gyppKWxnxclVy944HXr4sq',
            'passwordDecrypt' => 'test'
        ]);


        DB::table('User')->insert([
            'user_id' => 2,
            'first_name' => 'Moderateur',
            'last_name' => 'Vayetek',
            'gender' => 1,
            'mobile' => '777777',
            'email' => 'moderateur@vayetek.com',
            'email_verified' => 1,
            'qr_code' => '8sqafkqms2',
            'country_id' => 'TUN',
            'password' => '$2y$10$TO58VrLpV6YXOFkK24tCoubbDPG12OfPTDkgQEIBuE/4yOqx1JYB.',
            'passwordDecrypt' => 'test'
        ]);

        DB::table('User')->insert([
            'user_id' => 3,
            'first_name' => 'Orateur',
            'last_name' => 'Vayetek',
            'gender' => 1,
            'mobile' => '777777',
            'email' => 'orateur@vayetek.com',
            'email_verified' => 1,
            'qr_code' => '8sqafmsd3',
            'country_id' => 'TUN',
            'password' => '$2y$10$kK1IDI8b1dJwzcCYxnsAkODG9Kb0q1/mgKD4Unrl3O0z4W6MaLkjW',
            'passwordDecrypt' => 'test'
        ]);
    }
}

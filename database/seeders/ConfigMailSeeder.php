<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConfigMailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Config_Mail')->insert([
            'username' => "user vayetek 1",
            'password' => "test",
            'mail_name' => "test config mail",
            'mail_address' => "user@vayetek.com",
            'driver' => "test",
            'host' => "test",
            'port' => 4444,
            'encryption' => "test",
            'congress_id' => 1
        ]);
        DB::table('Config_Mail')->insert([
            'username' => "user vayetek 2",
            'password' => "test",
            'mail_name' => "test config mail",
            'mail_address' => "user@vayetek.com",
            'driver' => "test",
            'host' => "test",
            'port' => 4444,
            'encryption' => "test",
            'congress_id' => 2
        ]);
        DB::table('Config_Mail')->insert([
            'username' => "user vayetek 3",
            'password' => "test",
            'mail_name' => "test config mail",
            'mail_address' => "user@vayetek.com",
            'driver' => "test",
            'host' => "test",
            'port' => 4444,
            'encryption' => "test",
            'congress_id' => 3
        ]);
    }
}

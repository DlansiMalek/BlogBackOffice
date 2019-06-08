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
        DB::table('users')->insert([
            'first_name' => 'Vayetek',
            'last_name' => 'Team',
            'email' => 'contact@vayetek.com',
            'cin' => '11010203',
            'gender' => '1',
            'valide' => false,
            'profession' => 'doctor',
            'domain' => 'x',
            'establishment' => 'vayetek',
            'city_id' => 1,
            'address' => "Sfax, Tunis",
            'postal' => '3011',
            'tel' => '74125136',
            'mobile' => '20125136',
            'fax' => '74125136',
            'validation_code' => str_random(30),
            //'password' => bcrypt('secret'),
        ]);
    }
}

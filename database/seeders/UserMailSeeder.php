<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserMailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('User_Mail')->insert([
            'user_id' => 1,
            'mail_id' => 1,
            'status' => 0
        ]);
        DB::table('User_Mail')->insert([
            'user_id' => 2,
            'mail_id' => 1,
            'status' => 1
        ]);
    }
}

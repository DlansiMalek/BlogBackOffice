<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class Custom_SMSTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Custom_SMS')->insert([
            'title'=>'SMS-Title1',
            'Content'=>'SMS-Content1',
            'created_at'=>date("Y-m-d")
        ]);
    }
}

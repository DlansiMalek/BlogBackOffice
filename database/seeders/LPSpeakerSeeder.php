<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LPSpeakerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('LP_Speaker')->insert([
            'congress_id' => 1,
            'first_name' => 'Speaker',
            'last_name' => 'Test 1',
            'role' => 'Motivator'
        ]);
        DB::table('LP_Speaker')->insert([
            'congress_id' => 1,
            'first_name' => 'Speaker',
            'last_name' => 'Test 2',
            'role' => 'Speaker'
        ]);
        DB::table('LP_Speaker')->insert([
            'congress_id' => 1,
            'first_name' => 'Speaker',
            'last_name' => 'Test 3',
            'role' => 'Motivator'
        ]);
        DB::table('LP_Speaker')->insert([
            'congress_id' => 1,
            'first_name' => 'Speaker',
            'last_name' => 'Test 4',
            'role' => 'Speaker'
        ]);


        DB::table('Config_LP')->insert([
            'congress_id' => 2,
            'first_name' => 'Speaker',
            'last_name' => 'Test 1',
            'role' => 'Motivator'
        ]);
        DB::table('Config_LP')->insert([
            'congress_id' => 2,
            'first_name' => 'Speaker',
            'last_name' => 'Test 2',
            'role' => 'Speaker'
        ]);
        DB::table('Config_LP')->insert([
            'congress_id' => 2,
            'first_name' => 'Speaker',
            'last_name' => 'Test 3',
            'role' => 'Motivator'
        ]);
        DB::table('Config_LP')->insert([
            'congress_id' => 2,
            'first_name' => 'Speaker',
            'last_name' => 'Test 4',
            'role' => 'Speaker'
        ]);
        

        DB::table('Config_LP')->insert([
            'congress_id' => 3,
            'first_name' => 'Speaker',
            'last_name' => 'Test 1',
            'role' => 'Motivator'
        ]);
        DB::table('Config_LP')->insert([
            'congress_id' => 3,
            'first_name' => 'Speaker',
            'last_name' => 'Test 2',
            'role' => 'Speaker'
        ]);
        DB::table('Config_LP')->insert([
            'congress_id' => 3,
            'first_name' => 'Speaker',
            'last_name' => 'Test 3',
            'role' => 'Motivator'
        ]);
        DB::table('Config_LP')->insert([
            'congress_id' => 3,
            'first_name' => 'Speaker',
            'last_name' => 'Test 4',
            'role' => 'Speaker'
        ]);
        
    }
}

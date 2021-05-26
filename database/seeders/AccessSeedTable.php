<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccessSeedTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Access')->insert([
            'access_id' => 1,
            'name' => 'Access Test 1',
            'price' => 40,
            'duration' => 90,
            'max_places' => 120,
            'room' => 'Room 1 Arena',
            'start_date' => date("Y-m-d"),
            'end_date' => date("Y-m-d"),
            'show_in_register' => 1,
            'congress_id' => 1,
            'topic_id' => 1,
            'access_type_id' => 1,
            'description' => 'test description'
        ]);
        DB::table('Access')->insert([
            'access_id' => 2,
            'name' => 'Access Test 2',
            'price' => 0,
            'duration' => 90,
            'max_places' => 120,
            'room' => 'Room 2 Arena',
            'start_date' => date("Y-m-d"),
            'end_date' => date("Y-m-d"),
            'show_in_register' => 0,
            'congress_id' => 1,
            'topic_id' => 1,
            'access_type_id' => 2,
            'description' => 'test description'
        ]);
        DB::table('Access')->insert([
            'access_id' => 3,
            'name' => 'Access Test 3',
            'price' => 0,
            'duration' => 60,
            'max_places' => 50,
            'room' => 'Room 3 Arena',
            'start_date' => date("Y-m-d"),
            'end_date' => date("Y-m-d"),
            'show_in_register' => 1,
            'congress_id' => 1,
            'topic_id' => 2,
            'access_type_id' => 3,
            'description' => 'test description'
        ]);
        DB::table('Access')->insert([
            'access_id' => 4,
            'name' => 'Access Test 4',
            'price' => 40,
            'duration' => 90,
            'max_places' => 120,
            'room' => 'Room 4 Arena',
            'start_date' => date("Y-m-d"),
            'end_date' => date("Y-m-d"),
            'show_in_register' => 0,
            'congress_id' => 2,
            'topic_id' => 1,
            'access_type_id' => 1,
            'description' => 'test description'
        ]);
        DB::table('Access')->insert([
            'access_id' => 5,
            'name' => 'Access Test 5',
            'price' => 0,
            'duration' => 90,
            'max_places' => 120,
            'room' => 'Room 5 Arena',
            'start_date' => date("Y-m-d"),
            'end_date' => date("Y-m-d"),
            'show_in_register' => 1,
            'congress_id' => 2,
            'topic_id' => 2,
            'access_type_id' => 2,
            'description' => 'test description'
        ]);
        DB::table('Access')->insert([
            'access_id' => 6,
            'name' => 'Access Test 6',
            'price' => 0,
            'duration' => 60,
            'max_places' => 50,
            'room' => 'Room 6 Arena',
            'start_date' => date("Y-m-d"),
            'end_date' => date("Y-m-d"),
            'show_in_register' => 0,
            'congress_id' => 2,
            'topic_id' => 2,
            'access_type_id' => 3,
            'description' => 'test description'
        ]);
        DB::table('Access')->insert([
            'access_id' => 7,
            'name' => 'Access Test 7',
            'price' => 40,
            'duration' => 90,
            'max_places' => 120,
            'room' => 'Room 7 Arena',
            'start_date' => date("Y-m-d"),
            'end_date' => date("Y-m-d"),
            'show_in_register' => 0,
            'congress_id' => 3,
            'topic_id' => 1,
            'access_type_id' => 1,
            'description' => 'test description'
        ]);
        DB::table('Access')->insert([
            'access_id' => 8,
            'name' => 'Access Test 8',
            'price' => 0,
            'duration' => 90,
            'max_places' => 120,
            'room' => 'Room 8 Arena',
            'start_date' => date("Y-m-d"),
            'end_date' => date("Y-m-d"),
            'show_in_register' => 1,
            'congress_id' => 3,
            'topic_id' => 1,
            'access_type_id' => 2,
            'description' => 'test description'
        ]);
        DB::table('Access')->insert([
            'access_id' => 9,
            'name' => 'Access Test 9',
            'price' => 0,
            'duration' => 60,
            'max_places' => 50,
            'room' => 'Room 9 Arena',
            'start_date' => date("Y-m-d"),
            'end_date' => date("Y-m-d"),
            'show_in_register' => 0,
            'congress_id' => 3,
            'topic_id' => 2,
            'access_type_id' => 3,
            'description' => 'test description'
        ]);
        DB::table('Access')->insert([
            'access_id' => 10,
            'name' => 'Access Test Jeux',
            'start_date' => date("Y-m-d"),
            'end_date' => date("Y-m-d"),
            'congress_id' => 3,
            'access_type_id' => 4
        ]);
    }
}

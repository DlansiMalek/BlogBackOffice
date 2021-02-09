<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccessSpeakerSeedTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Access_Speaker')->insert([
            'user_id' => 1,
            'access_id' => 4
        ]);
        DB::table('Access_Speaker')->insert([
            'user_id' => 2,
            'access_id' => 3
        ]);
        DB::table('Access_Speaker')->insert([
            'user_id' => 3,
            'access_id' => 2
        ]);
        DB::table('Access_Speaker')->insert([
            'user_id' => 4,
            'access_id' => 1
        ]);
    }
}

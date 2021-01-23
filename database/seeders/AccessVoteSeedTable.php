<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccessVoteSeedTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Access_Vote')->insert([
            'vote_id' => '5b9bfcee33b1591300638b4d1',
            'access_id' => 1,
            'congress_id' => 1
        ]);
        DB::table('Access_Vote')->insert([
            'vote_id' => '5b9bfcee33b1591300638b4d2',
            'access_id' => 2,
            'congress_id' => 2
        ]);
        DB::table('Access_Vote')->insert([
            'vote_id' => '5b9bfcee33b1591300638b4d3',
            'access_id' => 3,
            'congress_id' => 3
        ]);
    }
}

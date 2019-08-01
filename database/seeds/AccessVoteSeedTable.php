<?php

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
            'vote_id' => '5cc345391aa309001282a83c',
            'access_id' => 1,
            'congress_id' => 1
        ]);
    }
}

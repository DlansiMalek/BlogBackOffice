<?php

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
            'user_id' => 3,
            'access_id' => 1
        ]);
    }
}

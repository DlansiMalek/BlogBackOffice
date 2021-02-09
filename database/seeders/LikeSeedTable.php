<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LikeSeedTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Like')->insert([
            'user_id' =>  1,
            'access_id' => 1
        ]);
        DB::table('Like')->insert([
            'user_id' =>  2,
            'access_id' => 2
        ]);
        DB::table('Like')->insert([
            'user_id' =>  3,
            'access_id' => 3
        ]);
    }
}

<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccessPackSeedTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Access_Pack')->insert([
            'pack_id' => 1,
            'access_id' => 1
        ]);
        DB::table('Access_Pack')->insert([
            'pack_id' => 2,
            'access_id' => 2
        ]);
        DB::table('Access_Pack')->insert([
            'pack_id' => 3,
            'access_id' => 3
        ]);
        DB::table('Access_Pack')->insert([
            'pack_id' => 4,
            'access_id' => 4
        ]);
        DB::table('Access_Pack')->insert([
            'pack_id' => 5,
            'access_id' => 5
        ]);
        DB::table('Access_Pack')->insert([
            'pack_id' => 6,
            'access_id' => 6
        ]);
        DB::table('Access_Pack')->insert([
            'pack_id' => 7,
            'access_id' => 7
        ]);
        DB::table('Access_Pack')->insert([
            'pack_id' => 8,
            'access_id' => 8
        ]);
        DB::table('Access_Pack')->insert([
            'pack_id' => 9,
            'access_id' => 9
        ]);
    }
}

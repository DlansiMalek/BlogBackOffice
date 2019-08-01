<?php

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
    }
}

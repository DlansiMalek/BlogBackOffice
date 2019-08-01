<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BadgeSeedTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Badge')->insert([
            'badge_id_generator' => '5c6dbd67d2cb3900015d7a65',
            'congress_id' => 1,
            'privilege_id' => 3
        ]);

        DB::table('Badge')->insert([
            'badge_id_generator' => '5c1b6ac304849a0001a5d83d',
            'congress_id' => 1,
            'privilege_id' => 2
        ]);
    }
}

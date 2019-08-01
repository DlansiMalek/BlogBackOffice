<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminCongressSeedTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::table('Admin_Congress')->insert([
            'admin_id' => 3,
            'congress_id' => 1,
            'privilege_id' => 1
        ]);

        //Adding Personalle d'un congrés
        DB::table('Admin_Congress')->insert([
            'admin_id' => 4,
            'congress_id' => 1,
            'privilege_id' => 2
        ]);

        DB::table('Admin_Congress')->insert([
            'admin_id' => 5,
            'congress_id' => 1,
            'privilege_id' => 7
        ]);

    }
}

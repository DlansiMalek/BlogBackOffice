<?php

namespace Database\Seeders;
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
            'privilege_id' => config('privilege.Admin')
        ]);

        //Adding Personalle d'un congrés
        DB::table('Admin_Congress')->insert([
            'admin_id' => 4,
            'congress_id' => 1,
            'privilege_id' => config('privilege.Organisateur')
        ]);

        DB::table('Admin_Congress')->insert([
            'admin_id' => 5,
            'congress_id' => 1,
            'privilege_id' => config('privilege.Organisme')
        ]);

    }
}

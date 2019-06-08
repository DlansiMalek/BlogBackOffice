<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccessTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Access_Type')->insert([
            'label' => 'Session (accessible par tous les participants)',
        ]);

        DB::table('Access_Type')->insert([
            'label' => 'Privé (accessible par les participants séléctionnés)',
        ]);
    }
}

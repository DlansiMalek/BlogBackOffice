<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PrivilegeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Privilege')->insert([
            'name' => 'Super Admin'
        ]);
        DB::table('Privilege')->insert([
            'name' => 'Organisateur'
        ]);
        DB::table('Privilege')->insert([
            'name' => 'Participant'
        ]);
        DB::table('Privilege')->insert([
            'name' => 'Conférencier'
        ]);
        DB::table('Privilege')->insert([
            'name' => 'Modérateur'
        ]);
        DB::table('Privilege')->insert([
            'name' => 'Invité'
        ]);
        DB::table('Privilege')->insert([
            'name' => 'Organisme (laboratoire, société, etc...)'
        ]);
        DB::table('Privilege')->insert([
            'name' => 'Orateur'
        ]);
    }
}

<?php

namespace Database\Seeders;
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
            'privilege_id' => 1,
            'name' => 'Admin'
        ]);
        DB::table('Privilege')->insert([
            'privilege_id' => 2,
            'name' => 'Organisateur'
        ]);
        DB::table('Privilege')->insert([
            'privilege_id' => 3,
            'name' => 'Participant'
        ]);
        DB::table('Privilege')->insert([
            'privilege_id' => 5,
            'name' => 'Modérateur'
        ]);
        DB::table('Privilege')->insert([
            'privilege_id' => 6,
            'name' => 'Invité'
        ]);
        DB::table('Privilege')->insert([
            'privilege_id' => 7,
            'name' => 'Organisme (laboratoire, société, etc)'
        ]);
        DB::table('Privilege')->insert([
            'privilege_id' => 8,
            'name' => 'Conférencier/Orateur'
        ]);
        DB::table('Privilege')->insert([
            'privilege_id' => 9,
            'name' => 'Super Admin'
        ]);
        DB::table('Privilege')->insert([
            'privilege_id' => 10,
            'name' => 'Marketing'
        ]);
        DB::table('Privilege')->insert([
            'privilege_id'=>11,
            'name'=>'Comite scientifique'
        ]);
        DB::table('Privilege')->insert([
            'privilege_id'=>12,
            'name'=>'Comite d`organisation'
        ]);
        DB::table('Privilege')->insert([
            'privilege_id'=>13,
            'name'=>'Comite de selection'
        ]);
    }
}

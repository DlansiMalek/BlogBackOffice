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
            'privilege_id' => config('privilege.Admin'),
            'name' => 'Admin'
        ]);
        DB::table('Privilege')->insert([
            'privilege_id' => config('privilege.Organisateur'),
            'name' => 'Organisateur'
        ]);
        DB::table('Privilege')->insert([
            'privilege_id' => config('privilege.Participant'),
            'name' => 'Participant'
        ]);
        DB::table('Privilege')->insert([
            'privilege_id' => config('privilege.Moderateur'),
            'name' => 'Modérateur'
        ]);
        DB::table('Privilege')->insert([
            'privilege_id' => config('privilege.Invite'),
            'name' => 'Invité'
        ]);
        DB::table('Privilege')->insert([
            'privilege_id' => config('privilege.Organisme'),
            'name' => 'Organisme (laboratoire, société, etc)'
        ]);
        DB::table('Privilege')->insert([
            'privilege_id' => config('privilege.Conferencier_Orateur'),
            'name' => 'Conférencier/Orateur'
        ]);
        DB::table('Privilege')->insert([
            'privilege_id' => config('privilege.Super_Admin'),
            'name' => 'Super Admin'
        ]);
        DB::table('Privilege')->insert([
            'privilege_id' => config('privilege.Marketing'),
            'name' => 'Marketing'
        ]);
        DB::table('Privilege')->insert([
            'privilege_id'=> config('privilege.Comite_scientifique'),
            'name'=>'Comite scientifique'
        ]);
        DB::table('Privilege')->insert([
            'privilege_id'=> config('privilege.Comite_d_organisation'),
            'name'=>'Comite d`organisation'
        ]);
        DB::table('Privilege')->insert([
            'privilege_id'=> config('privilege.Comite_de_selection'),
            'name'=>'Comite de selection'
        ]);
    }
}

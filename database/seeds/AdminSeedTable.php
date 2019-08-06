<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminSeedTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::table('Admin')->insert([
            'admin_id' => 1,
            'name' => 'SuperAdmin Vayetek',
            'email' => 'super_admin@vayetek.com',
            'mobile' => '77777777',
            'privilege_id' => 9,
            'passwordDecrypt' => 'SuperAdminVayetek',
            'password' => bcrypt('SuperAdminVayetek')
        ]);

        DB::table('Admin')->insert([
            'admin_id' => 2,
            'name' => 'Marketing Vayetek',
            'email' => 'marketing@vayetek.com',
            'mobile' => '77777777',
            'privilege_id' => 10,
            'passwordDecrypt' => 'MarketingVayetek',
            'password' => bcrypt('MarketingVayetek')
        ]);

        DB::table('Admin')->insert([
            'admin_id' => 3,
            'name' => 'Admin Vayetek',
            'email' => 'admin@vayetek.com',
            'mobile' => '77777777',
            'privilege_id' => 1,
            'passwordDecrypt' => 'AdminVayetek',
            'password' => bcrypt('AdminVayetek')
        ]);


        DB::table('Admin')->insert([
            'admin_id' => 4,
            'name' => 'Organisateur Vayetek',
            'email' => 'organisateur@vayetek.com',
            'mobile' => '77777777',
            'passwordDecrypt' => 'OrganisateurVayetek',
            'password' => bcrypt('ModerateurVayetek')
        ]);

        DB::table('Admin')->insert([
            'admin_id' => 5,
            'name' => 'Organisme Vayetek',
            'email' => 'organisme@vayetek.com',
            'mobile' => '77777777',
            'passwordDecrypt' => 'OrganismeVayetek',
            'password' => bcrypt('OrganismeVayetek')
        ]);

    }
}

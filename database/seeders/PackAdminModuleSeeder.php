<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PackAdminModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Pack_Admin_Module')->insert([
            'pack_admin_id' => 1,
            'module_id' => 1
        ]);
        DB::table('Pack_Admin_Module')->insert([
            'pack_admin_id' => 2,
            'module_id' => 2
        ]);
        DB::table('Pack_Admin_Module')->insert([
            'pack_admin_id' => 3,
            'module_id' => 3
        ]);
    }
}

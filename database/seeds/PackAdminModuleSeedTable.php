<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PackAdminModuleSeedTable extends Seeder
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
            'pack_admin_id' => 1,
            'module_id' => 2
        ]);
    }
}

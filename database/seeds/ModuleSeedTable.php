<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModuleSeedTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('Module')->insert([
            'type' => 'Participants'
        ]);
        DB::table('Module')->insert([
            'type' => 'Badge'
        ]);
        DB::table('Module')->insert([
            'type' => 'Organization'
        ]);
    }
}

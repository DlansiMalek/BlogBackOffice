<?php

use Illuminate\Database\Seeder;

class HistoryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('History_pack')->insert([
            'status' => 'Reserved',
            'pack_id' => 1,
            'admin_id' => 3 // il faut d'abord créer un admin avec cet ID
        ]);
        DB::table('History_pack')->insert([
            'status' => 'Reserved',
            'pack_id' => 2,
            'admin_id' => 3 // il faut d'abord créer un admin avec cet ID
        ]);
        DB::table('History_pack')->insert([
            'status' => 'paid',
            'pack_id' => 1,
            'admin_id' => 3 // il faut d'abord créer un admin avec cet ID
        ]);
    }
}

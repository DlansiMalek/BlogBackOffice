<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccessTypeSeedTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Access_Type')->insert([
            'label' => 'Session',
        ]);

        DB::table('Access_Type')->insert([
            'label' => 'Atelier',
        ]);
        DB::table('Access_Type')->insert([
            'label' => 'Pause',
        ]);
        DB::table('Access_Type')->insert([
            'label' => 'Jeux',
        ]);
    }
}

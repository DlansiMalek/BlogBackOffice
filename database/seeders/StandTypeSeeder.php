<?php

namespace Database\Seeders;
use Illuminate\Support\Facades\DB;

use Illuminate\Database\Seeder;

class StandTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Stand_Type')->insert([
            'stand_type_id'=> 1,
            'name'=> 'Stand 3x3',
            'preview_img'=> '',
            'is_publicity' => false,
            'is_fixed' => false
        ]);
        DB::table('Stand_Type')->insert([
            'stand_type_id'=> 2,
            'name'=> 'Stand 6x4',
            'preview_img'=> '',
            'is_publicity' => false,
            'is_fixed' => false
        ]);
        DB::table('Stand_Type')->insert([
            'stand_type_id'=> 3,
            'name'=> 'Stand 10x4',
            'preview_img'=> '',
            'is_publicity' => false,
            'is_fixed' => false
        ]);
        DB::table('Stand_Type')->insert([
            'stand_type_id'=> 4,
            'name'=> 'Stand 6x4 V2',
            'preview_img'=> '',
            'is_publicity' => false,
            'is_fixed' => false
        ]);
        DB::table('Stand_Type')->insert([
            'stand_type_id'=> 5,
            'name'=> 'Stand 15x8',
            'preview_img'=> '',
            'is_publicity' => false,
            'is_fixed' => false
        ]);
    }
}

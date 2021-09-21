<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Category')->insert([
            'category_id'=> 1,
            'label' => "Category test 1",
        ]);
        DB::table('Category')->insert([
            'category_id'=> 2,
            'label' => "Category test 2",
        ]);
        DB::table('Category')->insert([
            'category_id'=> 3,
            'label' => "Category test 3",
        ]);
    }
}

<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PackSeedTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Pack')->insert([
            'label' => 'Pack test 1',
            'description' => 'description pack',
            'price' => 250,
            'congress_id' => 1
        ]);
        DB::table('Pack')->insert([
            'label' => 'Pack test 2',
            'description' => 'description pack',
            'price' => 250,
            'congress_id' => 1
        ]);
        DB::table('Pack')->insert([
            'label' => 'Pack test 3',
            'description' => 'description pack',
            'price' => 350,
            'congress_id' => 1
        ]);
        DB::table('Pack')->insert([
            'label' => 'Pack test 4',
            'description' => 'description pack',
            'price' => 70,
            'congress_id' => 2
        ]);
        DB::table('Pack')->insert([
            'label' => 'Pack test 5',
            'description' => 'description pack',
            'price' => 50,
            'congress_id' => 2
        ]);
        DB::table('Pack')->insert([
            'label' => 'Pack test 6',
            'description' => 'description pack',
            'price' => 100,
            'congress_id' => 2
        ]);
        DB::table('Pack')->insert([
            'label' => 'Pack test 7',
            'description' => 'description pack',
            'price' => 90,
            'congress_id' => 3
        ]);
        DB::table('Pack')->insert([
            'label' => 'Pack test 8',
            'description' => 'description pack',
            'price' => 100,
            'congress_id' => 3
        ]);
        DB::table('Pack')->insert([
            'label' => 'Pack test 9',
            'description' => 'description pack',
            'price' => 140,
            'congress_id' => 3
        ]);

    }
}

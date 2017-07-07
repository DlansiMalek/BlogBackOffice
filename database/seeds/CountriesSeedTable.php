<?php

use Illuminate\Database\Seeder;

class CountriesSeedTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('countries')->insert([
            'name' => 'Tunis',
            'code' => 'TN',
        ]);
    }
}

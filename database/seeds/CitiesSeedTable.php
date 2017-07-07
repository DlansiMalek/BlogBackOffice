<?php

use Illuminate\Database\Seeder;

class CitiesSeedTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('cities')->insert([
            'name' => 'Sfax',
            'country_id' => 1,
        ]);
    }
}

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
            'country_id' => 218
        ]);
        DB::table('cities')->insert([
            'name' => 'Tunis',
            'country_id' => 218
        ]);
        DB::table('cities')->insert([
            'name' => 'Mednine',
            'country_id' => 218
        ]);
        DB::table('cities')->insert([
            'name' => 'Bizert',
            'country_id' => 218
        ]);
        DB::table('cities')->insert([
            'name' => 'Sousse',
            'country_id' => 218
        ]);
        DB::table('cities')->insert([
            'name' => 'Mestir',
            'country_id' => 218
        ]);
        DB::table('cities')->insert([
            'name' => 'Mahdia',
            'country_id' => 218
        ]);
        DB::table('cities')->insert([
            'name' => 'Nabeul',
            'country_id' => 218
        ]);
        DB::table('cities')->insert([
            'name' => 'Gabes',
            'country_id' => 218
        ]);
        DB::table('cities')->insert([
            'name' => 'Tataouin',
            'country_id' => 218
        ]);
        DB::table('cities')->insert([
            'name' => 'Ben Arous',
            'country_id' => 218
        ]);
        DB::table('cities')->insert([
            'name' => 'Gafsa',
            'country_id' => 218
        ]);
        DB::table('cities')->insert([
            'name' => 'Kef',
            'country_id' => 218
        ]);
        DB::table('cities')->insert([
            'name' => 'Jandouba',
            'country_id' => 218
        ]);
    }
}

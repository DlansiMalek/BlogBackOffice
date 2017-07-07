<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(CountriesSeedTable::class);
        $this->call(CitiesSeedTable::class);
        $this->call(UsersTableSeeder::class);
    }
}

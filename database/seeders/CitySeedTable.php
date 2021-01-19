<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CitySeedTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $pathDB = public_path('db/city_data.sql');

        DB::unprepared(file_get_contents($pathDB));

        $this->command->info('City table seeded!');
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PhoneCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $pathDB = public_path('db/phone_data.sql');

        DB::unprepared(file_get_contents($pathDB));

        $this->command->info('Country table seeded!');
    }
}

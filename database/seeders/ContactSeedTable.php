<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class ContactSeedTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Contact')->insert([
            'contact_id' => 1,
            'user_id' => 1,
            'user_viewed' => 2,
            'congress_id' => 1

        ]);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OffreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Offre')->insert([
            'name' => 'Mailing pro',
            'value' => 3000,
            'start_date' => '2020-11-08',
            'end_date'=> '2020-11-15',
            'status' => 1,
            'is_mail_pro' => 1,
            'offre_type_id' => 1,
            'admin_id' => 1
        ]);

        DB::table('Offre')->insert([
            'name' => 'Mailing pro',
            'value' => 3000,
            'start_date' => '2020-11-16',
            'end_date'=> '2020-11-20',
            'status' => 1,
            'is_mail_pro' => 1,
            'offre_type_id' => 1,
            'admin_id' => 1
        ]);
    }
}

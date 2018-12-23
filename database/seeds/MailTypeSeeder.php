<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MailTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Mail_Type.php')->insert([
            'name' => 'inscription',
        ]);

        DB::table('Mail_Type.php')->insert([
            'name' => 'paiement',
        ]);

        DB::table('Mail_Type.php')->insert([
            'name' => 'attestation',
        ]);

        DB::table('Mail_Type.php')->insert([
            'name' => 'custom',
        ]);
    }
}

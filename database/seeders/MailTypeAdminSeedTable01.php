<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MailTypeAdminSeedTable01 extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Mail_Type_Admin')->insert([
            'mail_type_admin_id' => 9,
            'name' => 'accept_landing_page_demand',
            'display_name' => 'Acceptation de la demande du Landing Page'
        ]);
        DB::table('Mail_Type_Admin')->insert([
            'mail_type_admin_id' => 10,
            'name' => 'refuse_landing_page_demand',
            'display_name' => 'Refus de la demande de Landing Page'
        ]);
    }
}

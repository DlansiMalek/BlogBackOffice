<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MailMeetingTypeAnnulationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Mail_Type')->insert([
            'mail_type_id' => 27,
            'name' => 'annulation_meeting',
            'display_name' => "Annulation de la réunion",
            'type' => "event"
        ]);
    }
}

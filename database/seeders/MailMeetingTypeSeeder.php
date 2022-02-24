<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MailMeetingTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Mail_Type')->insert([
            'mail_type_id' => 24,
            'name' => 'request_meeting',
            'display_name' => "Demande de réunion ",
            'type' => "event"
        ]);
        DB::table('Mail_Type')->insert([
            'mail_type_id' => 25,
            'name' => 'accept_meeting',
            'display_name' => "Acceptation de la demande de réunion",
            'type' => "event"
        ]);
        DB::table('Mail_Type')->insert([
            'mail_type_id' => 26,
            'name' => 'decline_meeting',
            'display_name' => "Refus de la demande de réunion",
            'type' => "event"
        ]);
    }
}

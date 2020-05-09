<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class RoomSeedTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Room')->insert([
            "name" => "Room test",
            "moderator_token" => "w1nXFoAzVQR0nm3L99FZkrCDWFqlD0Kw2Dp9loUy",
            "invitee_token" => "w1nXFoAzVQR0nm3L99FZkrCDWFqlD0Kw2Dp9loUa",
            "admin_id" => 3
        ]);
    }
}

<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserCongressSeedTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('User_Congress')->insert([
            'user_id' => 1,
            'privilege_id' => config('privilege.Participant'),
            'congress_id' => 1
        ]);
        DB::table('User_Congress')->insert([
            'user_id' => 2,
            'privilege_id' => config('privilege.Moderateur'),
            'congress_id' => 1
        ]);
        DB::table('User_Congress')->insert([
            'user_id' => 3,
            'privilege_id' => config('privilege.Conferencier_Orateur'),
            'congress_id' => 1
        ]);
        DB::table('User_Congress')->insert([
            'user_id' => 4,
            'privilege_id' => config('privilege.Invite'),
            'congress_id' => 1
        ]);
        DB::table('User_Congress')->insert([
            'user_id' => 1,
            'privilege_id' => config('privilege.Participant'),
            'congress_id' => 2
        ]);
        DB::table('User_Congress')->insert([
            'user_id' => 2,
            'privilege_id' => config('privilege.Moderateur'),
            'congress_id' => 2
        ]);
        DB::table('User_Congress')->insert([
            'user_id' => 3,
            'privilege_id' => config('privilege.Conferencier_Orateur'),
            'congress_id' => 2
        ]);
        DB::table('User_Congress')->insert([
            'user_id' => 4,
            'privilege_id' => config('privilege.Invite'),
            'congress_id' => 2
        ]);
        DB::table('User_Congress')->insert([
            'user_id' => 1,
            'privilege_id' => config('privilege.Participant'),
            'congress_id' => 3
        ]);
        DB::table('User_Congress')->insert([
            'user_id' => 2,
            'privilege_id' => config('privilege.Moderateur'),
            'congress_id' => 3
        ]);
        DB::table('User_Congress')->insert([
            'user_id' => 3,
            'privilege_id' => config('privilege.Conferencier_Orateur'),
            'congress_id' => 3
        ]);
        DB::table('User_Congress')->insert([
            'user_id' => 4,
            'privilege_id' => config('privilege.Invite'),
            'congress_id' => 3
        ]);
    }
}

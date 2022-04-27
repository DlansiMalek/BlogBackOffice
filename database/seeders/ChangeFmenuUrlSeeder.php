<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\DB;
class ChangeFmenuUrlSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('FMenu')->where('key', '=', 'Tables')->update([
            'url' => 'b2b/buttons'
        ]);
    }
}

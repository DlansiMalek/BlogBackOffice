<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateStandContentConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Db::table('Stand_Content_Config')->where('accept_file', '=', 'jpg;png;jpeg')
        ->update(['accept_file'=>'.jpg;.png;.jpeg']);
    }
}

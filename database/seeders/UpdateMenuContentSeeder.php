<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateMenuContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Db::table('Menu')->where('menu_id', '=', '3')->update(['index'=>'5']);
        Db::table('Menu')->where('menu_id', '=', '4')->update(['index'=>'6']);
        Db::table('Menu')->where('menu_id', '=', '5')->update(['index'=>'7']);
        Db::table('Menu')->where('menu_id', '=', '6')->update(['index'=>'8']);
        Db::table('Menu')->where('menu_id', '=', '7')->update(['index'=>'9']);
        Db::table('Menu')->where('menu_id', '=', '8')->update(['index'=>'10']);
        Db::table('Menu')->where('menu_id', '=', '9')->update(['index'=>'11']);
        Db::table('Menu')->where('menu_id', '=', '10')->update(['index'=>'12']);
        Db::table('Menu')->where('menu_id', '=', '11')->update(['index'=>'13']);
        Db::table('Menu')->where('menu_id', '=', '12')->update(['index'=>'14']);
        Db::table('Menu')->where('menu_id', '=', '13')->update(['index'=>'15']);
        Db::table('Menu')->where('menu_id', '=', '14')->update(['index'=>'16']);
        Db::table('Menu')->where('menu_id', '=', '15')->update(['index'=>'17']);
        Db::table('Menu')->where('menu_id', '=', '16')->update(['index'=>'18']);

        DB::table('Menu')->insert([
            'menu_id' => 18,
            'key' => "GConfiguration",
            'icon' => "fas fa-cogs",
            'index' => 4
        ]);      
    }
}

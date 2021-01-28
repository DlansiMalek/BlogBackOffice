<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class CurrencySeedTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Currency')->insert([
            'code' => 'TND',
            'Label' => 'Dinar Tunisien'
        ]);
    }
}

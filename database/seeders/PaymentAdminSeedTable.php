<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentAdminSeedTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('Payment_Admin')->insert([
            'isPaid' => 0,
            'reference' => 'testReference',
            'authorization' => 'testauth ',
            'price' => 50,
            'path' => '/path/to/file ' ,
            'offre_id'=> 1,
            'admin_id' => 3
        ]);
        DB::table('Payment_Admin')->insert([
            'isPaid' => 0,
            'reference' => 'testReference2',
            'authorization' => 'testauth 2',
            'price' => 60,
            'path' => '/path/to/file2 ' ,
            'offre_id'=> 1,
            'admin_id' => 3
        ]);
    }
}

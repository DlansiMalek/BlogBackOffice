<?php

use Illuminate\Database\Seeder;

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
        DB::table('Payment_admin')->insert([
            'isPaid' => 0,
            'reference' => 'testReference',
            'authorization' => 'testauth ',
            'price' => 50,
            'path' => '/path/to/file ' ,
            'pack_id' => 1 ,
            'admin_id' => 3
        ]);
        DB::table('Payment_admin')->insert([
            'isPaid' => 0,
            'reference' => 'testReference2',
            'authorization' => 'testauth 2',
            'price' => 60,
            'path' => '/path/to/file2 ' ,
            'pack_id' => 2 ,
            'admin_id' => 3
        ]);
    }
}

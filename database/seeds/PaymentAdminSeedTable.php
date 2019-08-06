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
            'reference' => '',
            'authorization' => '',
            'price' => 50,
            'path' => '' ,
            'pack_id' => 1 ,
            'admin_id' => 3
        ]);
        DB::table('Payment_admin')->insert([
            'isPaid' => 0,
            'reference' => '',
            'authorization' => '',
            'price' => 60,
            'path' => '' ,
            'pack_id' => 2 ,
            'admin_id' => 3
        ]);
    }
}

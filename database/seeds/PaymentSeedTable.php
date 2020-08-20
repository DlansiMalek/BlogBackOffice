<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentSeedTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Payment')->insert([
            'isPaid' => 1,
            'price' => 200,
            'user_id' => 1,
            'congress_id' => 1,
            'payment_type_id' => 1
        ]);
        DB::table('Payment')->insert([
            'isPaid' => 0,
            'price' => 0,
            'user_id' => 2,
            'congress_id' => 2,
            'payment_type_id' => 1
        ]);
        DB::table('Payment')->insert([
            'isPaid' => 0,
            'price' => 0,
            'user_id' => 3,
            'congress_id' => 3,
            'payment_type_id' => 2
        ]);
    }
}

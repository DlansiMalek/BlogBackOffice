<?php

namespace Database\Seeders;
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
            'isPaid' => 0,
            'price' => 120,
            'user_id' => 1,
            'congress_id' => 1,
            'payment_type_id' => 1
        ]);
    }
}

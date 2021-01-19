<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Payment_Type')->insert([
            'name' => 'cash',
            'display_name'=>'Cash'
        ]);

        DB::table('Payment_Type')->insert([
            'name' => 'check',
            'display_name'=>'Chèque'
        ]);

        DB::table('Payment_Type')->insert([
            'name' => 'transfer',
            'display_name'=>'Transfert Bancaire'
        ]);

        DB::table('Payment_Type')->insert([
            'name' => 'online',
            'display_name'=>'En Ligne'
        ]);
    }
}

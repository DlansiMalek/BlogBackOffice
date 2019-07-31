<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(CountriesSeedTable::class);
        $this->call(PrivilegeTableSeeder::class);
        $this->call(MailTypeSeeder::class);
        $this->call(FormInputTypeSeeder::class);
        $this->call(PaymentTypeSeeder::class);
        $this->call(AccessTypeSeeder::class);
        $this->call(AttestationTypeSeeder::class);
        $this->call(PackAdminTableSeeder::class);
        $this->call(ModuleTableSeeder::class);
        
    }
}

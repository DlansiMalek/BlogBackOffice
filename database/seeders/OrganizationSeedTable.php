<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrganizationSeedTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Organization')->insert([
            'name' => 'Organization test',
            'description' => 'description',
            'mobile' => '777777'
        ]);
    }
}

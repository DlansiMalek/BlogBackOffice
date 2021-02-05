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
            'name' => 'Organization test 1',
            'description' => 'description 1',
            'mobile' => '1111111'
        ]);
        DB::table('Organization')->insert([
            'name' => 'Organization test 2 ',
            'description' => 'description 2',
            'mobile' => '2222222'
        ]);
        DB::table('Organization')->insert([
            'name' => 'Organization test 3',
            'description' => 'description 3',
            'mobile' => '33333333'
        ]);
    }
}

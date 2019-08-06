<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TopicSeedTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Topic')->insert([
            'label' => 'Santé',
        ]);

        DB::table('Topic')->insert([
            'label' => 'Sport',
        ]);
    }
}

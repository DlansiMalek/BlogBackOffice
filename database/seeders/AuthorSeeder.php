<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AuthorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Author')->insert([
            'first_name' => 'auteur 1',
            'last_name' => 'test',
            'email' => 'author1@eventizer.io',
            'rank' => '1',
            'submission_id' => '1',
            'service_id' => '1',
            'etablissement_id' => '1'
        ]);
        DB::table('Author')->insert([
            'first_name' => 'auteur 2',
            'last_name' => 'test',
            'email' => 'author2@eventizer.io',
            'rank' => '1',
            'submission_id' => '2',
            'service_id' => '2',
            'etablissement_id' => '2'
        ]);
        DB::table('Author')->insert([
            'first_name' => 'auteur 3',
            'last_name' => 'test',
            'email' => 'author3@eventizer.io',
            'rank' => '1',
            'submission_id' => '3',
            'service_id' => '3',
            'etablissement_id' => '3'
        ]);
    }
}

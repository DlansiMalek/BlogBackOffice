<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Project')->insert([
            'nom' => "Project test 1",
            'date' => date("Y-m-d"),
            'project_img' => 'bRZfBIQiWcDEkbp3iiBThJWcxUm3QukqrVDgVxdp.jpg',
            'lien' => null,
            'admin_id' => 2,
            'category_id' => 1,

        ]);
        DB::table('Project')->insert([
            'nom' => "Project test 2",
            'date' => date("Y-m-d"),
            'project_img' => 'bRZfBIQiWcDEkbp3iiBThJWcxUm3QukqrVDgVxdp.jpg',
            'lien' => null,
            'admin_id' => 3,
            'category_id' => 1,
        ]);
        DB::table('Project')->insert([
            'nom' => "Project test 3",
            'date' => date("Y-m-d"),
            'project_img' => 'bRZfBIQiWcDEkbp3iiBThJWcxUm3QukqrVDgVxdp.jpg',
            'lien' => null,
            'admin_id' => 2,
            'category_id' => 2,

        ]);
        DB::table('Project')->insert([
            'nom' => "Project test 4",
            'date' => date("Y-m-d"),
            'project_img' => 'bRZfBIQiWcDEkbp3iiBThJWcxUm3QukqrVDgVxdp.jpg',
            'lien' => null,
            'admin_id' => 3,
            'category_id' => 2,

        ]);
        DB::table('Project')->insert([
            'nom' => "Project test 5",
            'date' => date("Y-m-d"),
            'project_img' => 'bRZfBIQiWcDEkbp3iiBThJWcxUm3QukqrVDgVxdp.jpg',
            'lien' => null,
            'admin_id' => 4,
            'category_id' => 3,

        ]);
        DB::table('Project')->insert([
            'nom' => "Project test 6",
            'date' => date("Y-m-d"),
            'project_img' => 'bRZfBIQiWcDEkbp3iiBThJWcxUm3QukqrVDgVxdp.jpg',
            'lien' => null,
            'admin_id' => 4,
            'category_id' => 3,

        ]);

    }
}

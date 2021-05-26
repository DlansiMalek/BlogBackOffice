<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class EvaluationInscriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Evaluation_Inscription')->insert([
            'admin_id' => 1,
            'user_id' => 1,
            'congress_id' => 1,
            'note' => 7,
            'commentaire' => 'faible'
        ]);
        DB::table('Evaluation_Inscription')->insert([
            'admin_id' => 2,
            'user_id' => 2,
            'congress_id' => 2,
            'note' => 16,
            'commentaire' => 'bien'
        ]);
        DB::table('Evaluation_Inscription')->insert([
            'admin_id' => 3,
            'user_id' => 3,
            'congress_id' => 3,
            'note' => 13,
            'commentaire' => 'bien'
        ]);
    }
}

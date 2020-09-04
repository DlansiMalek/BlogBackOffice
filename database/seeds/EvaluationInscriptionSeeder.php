<?php

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
            'note' => 10,
            'commentaire' => 'bien'
        ]);
    }
}

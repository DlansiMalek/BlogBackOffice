<?php

use Illuminate\Database\Seeder;

class SubmissionModuleSeeders extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(ThemeTableSeeder::class);
        $this->call(SubmissionTableSeeder::class);
        $this->call(SubmissionEvaluationTableSeeder::class);
        $this->call(CongressThemeTableSeeder::class);
        $this->call(EtablissementSeeder::class);
        $this->call(ServiceSeeder::class);
    }
}

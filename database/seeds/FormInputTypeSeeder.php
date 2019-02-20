<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FormInputTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Form_Input_Type')->insert([
            'name' => 'text',
            'display_name' => 'Texte',
        ]);

        DB::table('Form_Input_Type')->insert([
            'name' => 'textarea',
            'display_name' => 'Paragraphe',

        ]);

        DB::table('Form_Input_Type')->insert([
            'name' => 'date',
            'display_name' => 'Date',
        ]);

        DB::table('Form_Input_Type')->insert([
            'name' => 'time',
            'display_name' => 'Temps',

        ]);

        DB::table('Form_Input_Type')->insert([
            'name' => 'datetime',
            'name' => 'Date et Temps',
        ]);

        DB::table('Form_Input_Type')->insert([
            'name' => 'checklist',
            'display_name' => 'Cases à cocher',
        ]);

        DB::table('Form_Input_Type')->insert([
            'name' => 'multiselect',
            'display_name' => 'Liste déroulante',
        ]);

        DB::table('Form_Input_Type')->insert([
            'name' => 'select',
            'display_name' => 'Liste déroulante avec choix multiple',
        ]);

        DB::table('Form_Input_Type')->insert([
            'name' => 'Radio',
            'display_name' => 'radio',
        ]);

        DB::table('Form_Input_Type')->insert([
            'name' => 'file',
            'display_name' => 'Fichier',

        ]);

        DB::table('Form_Input_Type')->insert([
            'name' => 'Fichiers multiples',
        ]);
    }
}

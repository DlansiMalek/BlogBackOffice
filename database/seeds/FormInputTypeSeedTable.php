<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FormInputTypeSeedTable extends Seeder
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
            'display_name' => 'Date et Temps',
        ]);

        DB::table('Form_Input_Type')->insert([
            'name' => 'checklist',
            'display_name' => 'Cases à cocher',
        ]);

        DB::table('Form_Input_Type')->insert([
            'name' => 'select',
            'display_name' => 'Liste déroulante',
        ]);


        //Id 8
        DB::table('Form_Input_Type')->insert([
            'name' => 'multiselect',
            'display_name' => 'Liste déroulante avec choix multiple',
        ]);

        DB::table('Form_Input_Type')->insert([
            'name' => 'radio',
            'display_name' => 'Radio',
        ]);
        DB::table('Form_Input_Type')->insert([
            'name' => 'image',
            'display_name' => 'Image',
        ]);
    }
}

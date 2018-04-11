<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEvaluationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Evaluation', function (Blueprint $table) {
            $table->increments('evaluation_id');


            $table->float('note');
            $table->string('remarque', 500);

            $table->integer('s_periode_id')->unsigned();
            $table->foreign('s_periode_id')->references('s_periode_id')->on('S_Periode');

            $table->integer('etudiant_id')->unsigned();
            $table->foreign('etudiant_id')->references('etudiant_id')->on('Etudiant');

            $table->integer('enseignant_id')->unsigned();
            $table->foreign('enseignant_id')->references('enseignant_id')->on('Enseignant');

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Evaluation');
    }
}

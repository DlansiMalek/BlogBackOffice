<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChoixGroupeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Choix_Groupe', function (Blueprint $table) {
            $table->increments('choix_groupe_id');

            $table->tinyInteger('choice');
            $table->tinyInteger('real_affect');


            $table->integer('etudiant_id')->unsigned();
            $table->foreign('etudiant_id')->references('etudiant_id')->on('Etudiant');


            $table->integer('groupe_id')->unsigned();
            $table->foreign('groupe_id')->references('groupe_id')->on('Groupe');


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
        Schema::dropIfExists('Choix_Groupe');
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGroupeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Groupe', function (Blueprint $table) {
            $table->increments('groupe_id');

            $table->string('label');
            $table->integer("capacity"); //Nombre des etudiants de ce groupe


            $table->integer('session_stage_id')->unsigned();
            $table->foreign('session_stage_id')->references('session_stage_id')->on('Session_Stage');

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
        Schema::dropIfExists('Groupe');
    }
}

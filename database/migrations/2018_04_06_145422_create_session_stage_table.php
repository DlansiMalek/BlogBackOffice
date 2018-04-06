<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSessionStageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Session_Stage', function (Blueprint $table) {
            $table->increments('session_stage_id');

            $table->string("name");
            $table->integer("capacity"); //Nombre des etudiants de cette session

            $table->date('date_choice_open');
            $table->date('date_choice_close');

            $table->date('date_service_open')->nullable();
            $table->date('date_service_close')->nullable();

            $table->integer('niveau_id')->unsigned();
            $table->foreign('niveau_id')->references('niveau_id')->on('Niveau');


            $table->timestamps();
            $table->softDeletes();


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Session_Stage');
    }
}

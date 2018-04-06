<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSgroupeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('S_Groupe', function (Blueprint $table) {
            $table->increments('s_groupe_id');

            $table->string('label');

            $table->integer("capacity"); //Nombre des etudiants de ce sous groupe

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
        Schema::dropIfExists('sgroupe');
    }
}

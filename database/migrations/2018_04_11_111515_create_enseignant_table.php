<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEnseignantTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Enseignant', function (Blueprint $table) {
            $table->increments('enseignant_id');
            $table->integer('CIN');
            $table->string('nom', 100);
            $table->string('prenom', 100);
            $table->string('email', 100);
            $table->string('qr_code', 100);


            $table->integer('service_id')->unsigned();
            $table->foreign('service_id')->references('service_id')->on('Service');

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
        Schema::dropIfExists('Enseignant');
    }
}

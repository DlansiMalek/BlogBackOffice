<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEtudiantTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Etudiant', function (Blueprint $table) {
            $table->increments('etudiant_id');
            $table->string('nom', 100);
            $table->string('prenom', 100);
            $table->integer('CIN');
            $table->integer('carte_Etudiant');
            $table->string('email', 100);
            $table->string('qr_code', 100);


            $table->integer('niveau_id')->unsigned();
            $table->foreign('niveau_id')->references('niveau_id')->on('Niveau');

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
        Schema::dropIfExists('Etudiant');
    }
}

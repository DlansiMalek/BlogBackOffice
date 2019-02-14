<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAttestationDivers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Attestation_Divers', function (Blueprint $table) {
            $table->increments('attestation_divers_id');
            $table->string("attestation_generator_id");

            $table->integer('attestation_type_id')->unsigned();
            $table->foreign('attestation_type_id')->references('attestation_type_id')->on('Attestation_Type')
                ->onDelete('cascade');

            $table->integer('congress_id')->unsigned();
            $table->foreign('congress_id')->references('congress_id')->on('Congress')
                ->onDelete('cascade');

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
        //
    }
}

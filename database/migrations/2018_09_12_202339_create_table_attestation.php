<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableAttestation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Attestation', function (Blueprint $table) {
            $table->increments('attestation_id');
            $table->string("attestation_generator_id_blank");
            $table->string("attestation_generator_id");

            $table->integer('congress_id')->unsigned();
            $table->foreign('congress_id')->references('congress_id')->on('Congress')
                ->onDelete('cascade');

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

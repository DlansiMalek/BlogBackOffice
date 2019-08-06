<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAttestationAccessTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Attestation_Access', function (Blueprint $table) {
            $table->increments('attestation_access_id');
            $table->string("attestation_generator_id");

            $table->integer('access_id')->unsigned();
            $table->foreign('access_id')->references('access_id')->on('Access')
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
        Schema::dropIfExists('Attestation_Access');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAttestationSubmissionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Attestation_Submission', function (Blueprint $table) {
            $table->increments('attestation_submission_id');
            $table->string("attestation_generator_id_blank")->nullable()->default(null);
            $table->string("attestation_generator_id")->nullable()->default(null);
            $table->unsignedTinyInteger('enable')->default(0);

            $table->integer('congress_id')->unsigned();
            $table->foreign('congress_id')->references('congress_id')->on('Congress')
                ->onDelete('cascade');
            $table->integer('communication_type_id')->unsigned();
            $table->foreign('communication_type_id')->references('communication_type_id')->on('Communication_Type')
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
        Schema::dropIfExists('Attestation_Submission');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCommunicationTypeIdToSubmissionEvaluation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Submission_Evaluation', function (Blueprint $table) {
            $table->unsignedInteger('communication_type_id')->nullable()->default(null);
            $table->foreign('communication_type_id')
            ->references('communication_type_id')
            ->on('Communication_Type')
            ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Submission_Evaluation', function (Blueprint $table) {
            $table->dropColumn('communication_type_id');
        });
    }
}

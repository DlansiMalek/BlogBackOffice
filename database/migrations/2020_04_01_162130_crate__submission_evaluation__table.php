<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CrateSubmissionEvaluationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Submission_Evaluation',function(Blueprint $table){
            $table->bigIncrements('submission_evaluation_id');
            $table->unsignedBigInteger('submission_id');
            $table->foreign('submission_id')->references('submission_id')->on('Submission');
            $table->unsignedInteger('admin_id');
            $table->foreign('admin_id')->references('admin_id')->on('Admin');
            $table->integer('note')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Submission_Evaluation');
    }
}

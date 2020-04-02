<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateResourceSubmissionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Resource_Submission', function (Blueprint $table) {
            $table->increments('resource_submission_id');
            $table->unsignedBigInteger('submission_id');
            $table->foreign('submission_id')->references('submission_id')->on('Submission')
            ->onDelete('cascade');
            $table->unsignedInteger('resource_id');
            $table->foreign('resource_id')->references('resource_id')->on('Resource')
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
        Schema::dropIfExists('Resource_Submission');
    }
}

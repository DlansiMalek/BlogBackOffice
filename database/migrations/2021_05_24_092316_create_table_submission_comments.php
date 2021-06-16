<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableSubmissionComments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Submission_Comments', function (Blueprint $table) {
            $table->increments('submission_comments_id');
            $table->text('description');
            $table->unsignedBigInteger('submission_id');
            $table->foreign('submission_id')->references('submission_id')
                ->on('Submission')
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
        Schema::table('Submission_Comments', function (Blueprint $table) {
            $table->dropForeign(['submission_id']);
        });
        Schema::dropIfExists('Submission_Comments');
    }
}

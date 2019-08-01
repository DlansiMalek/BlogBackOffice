<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFeedbackQuestionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Feedback_Question', function (Blueprint $table) {
            $table->increments('feedback_question_id');
            $table->string('question');
            $table->unsignedTinyInteger('isText');

            $table->unsignedInteger('congress_id')->nullable()->default(null);
            $table->foreign('congress_id')->references('congress_id')->on('Congress')->onDelete('cascade');

            $table->unsignedInteger('access_id')->nullable()->default(null);
            $table->foreign('access_id')->references('access_id')->on('Access')->onDelete('cascade');

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
        Schema::dropIfExists('feedback_question');
    }
}

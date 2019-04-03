<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFeedbackResponseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Feedback_Response', function (Blueprint $table) {
            $table->increments('feedback_response_id');
            $table->string('text')->nullable();
            $table->unsignedInteger('feedback_question_value_id')->nullable();
            $table->unsignedInteger("user_id");
            $table->unsignedInteger('feedback_question_id');
            $table->foreign('feedback_question_id')->references('feedback_question_id')->on('Feedback_Question');
            $table->foreign('feedback_question_value_id')->references('feedback_question_value_id')->on('Feedback_Question_Value');
            $table->foreign('user_id')->references('user_id')->on('User');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('feedback_response');
    }
}

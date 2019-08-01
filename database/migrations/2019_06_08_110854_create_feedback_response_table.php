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
            $table->string('value')
                ->nullable();

            $table->unsignedInteger('feedback_value_id')->nullable()->default(null);
            $table->foreign('feedback_value_id')->references('feedback_value_id')->on('Feedback_Value')->onDelete('cascade');

            $table->unsignedInteger('feedback_question_id');
            $table->foreign('feedback_question_id')->references('feedback_question_id')->on('Feedback_Question')->onDelete('cascade');

            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('user_id')->on('User')->onDelete('cascade');

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
        Schema::dropIfExists('feedback_response');
    }
}

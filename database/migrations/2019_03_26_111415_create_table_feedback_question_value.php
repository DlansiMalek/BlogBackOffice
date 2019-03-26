<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableFeedbackQuestionValue extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Feedback_Question_Value', function (Blueprint $table) {
            $table->increments('feedback_question_id');
            $table->string("value");
            $table->unsignedInteger("feedback_question_id");
            $table->foreign("feedback_question_id")->references("feedback_question_id")->on("Feedback_Question");

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
        //
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFeedbackValueTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Feedback_Value', function (Blueprint $table) {
            $table->increments('feedback_value_id');
            $table->string('value');

            $table->unsignedInteger('feedback_question_id');
            $table->foreign('feedback_question_id')->references('feedback_question_id')->on('Feedback_Question')->onDelete('cascade');

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
        Schema::table('Feedback_Value', function (Blueprint $table) {
            $table->dropForeign(['feedback_question_id']);
        });
        Schema::dropIfExists('Feedback_Value');
    }
}

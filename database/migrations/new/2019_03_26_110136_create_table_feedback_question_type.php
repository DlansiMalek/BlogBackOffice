<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableFeedbackQuestionType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Feedback_Question_Type', function (Blueprint $table) {
            $table->increments('feedback_question_type_id');
            $table->string("value");
            $table->unsignedInteger("form_input_id");
            $table->foreign("form_input_id")->references("form_input_id")->on("Form_Input");

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

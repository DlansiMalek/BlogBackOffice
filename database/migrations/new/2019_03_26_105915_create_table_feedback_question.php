<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableFeedbackQuestion extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Feedback_Question',function (Blueprint $table){
            $table->increments("feedback_question_id");
            $table->string("label");
            $table->unsignedInteger("congress_id");
            $table->unsignedInteger('max_responses')->nullable()->default(1);
            $table->unsignedInteger('feedback_question_type_id');
            $table->foreign('congress_id')->references("congress_id")->on("Congress");
            $table->foreign('feedback_question_type_id')->references("feedback_question_type_id")->on("Feedback_Question_Type");

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

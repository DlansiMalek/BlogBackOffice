<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableResponseReference extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Response_Reference', function (Blueprint $table) {
            $table->increments('response_reference_id');

            $table->unsignedInteger('form_input_value_id');
            $table->foreign('form_input_value_id')->references('form_input_value_id')
                ->on('Form_Input_Value')
                ->onDelete('cascade');

            $table->unsignedInteger('question_reference_id');
            $table->foreign('question_reference_id')->references('question_reference_id')
                ->on('Question_Reference')
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
        Schema::table('Response_Reference', function (Blueprint $table) {
            $table->dropForeign(['form_input_value_id']);
            $table->dropForeign(['question_reference_id']);
        });
        Schema::dropIfExists('Response_Reference');
    }
}

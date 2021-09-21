<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableQuestionReference extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Question_Reference', function (Blueprint $table) {
            $table->increments('question_reference_id');

            $table->unsignedInteger('reference_id');
            $table->foreign('reference_id')->references('form_input_id')
                ->on('Form_Input')
                ->onDelete('cascade');

            $table->unsignedInteger('form_input_id');
            $table->foreign('form_input_id')->references('form_input_id')
                ->on('Form_Input')
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
        Schema::table('Question_Reference', function (Blueprint $table) {
            $table->dropForeign(['reference_id']);
            $table->dropForeign(['form_input_id']);
        });
        Schema::dropIfExists('Question_Reference');
    }
}

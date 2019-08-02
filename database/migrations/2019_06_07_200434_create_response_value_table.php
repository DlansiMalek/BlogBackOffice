<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateResponseValueTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Response_Value', function (Blueprint $table) {
            $table->increments('response_value_id');

            $table->unsignedInteger('form_input_response_id');
            $table->foreign('form_input_response_id')->references('form_input_response_id')->on("Form_Input_Response")->onDelete('cascade');

            $table->unsignedInteger('form_input_value_id');
            $table->foreign('form_input_value_id')->references('form_input_value_id')->on("Form_Input_Value")->onDelete('cascade');


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
        Schema::table('Response_Value', function (Blueprint $table) {
            $table->dropForeign(['form_input_response_id']);
            $table->dropForeign(['form_input_value_id']);
        });
        Schema::dropIfExists('Response_Value');
    }
}

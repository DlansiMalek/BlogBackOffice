<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFormInputResponseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Form_Input_Response', function (Blueprint $table) {
            $table->increments('form_input_response_id');
            $table->string('response')
                ->nullable()->default(null);

            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('user_id')->on("User")->onDelete('cascade');

            $table->unsignedInteger('form_input_id');
            $table->foreign('form_input_id')->references('form_input_id')->on("Form_Input")->onDelete('cascade');


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
        Schema::table('Form_Input_Response', function (Blueprint $table) {
            $table->dropForeign(['form_input_id']);
            $table->dropForeign(['user_id']);
        });
        Schema::dropIfExists('Form_Input_Response');
    }
}

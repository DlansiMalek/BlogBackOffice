<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableFormInputReponse extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Form_Input_Reponse', function (Blueprint $table) {
            $table->increments('form_input_reponse_id');
            $table->string('reponse');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('form_input_id');
            $table->foreign('user_id')->references('user_id')->on("User");
            $table->foreign('form_input_id')->references('form_input_id')->on("Form_Input");

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

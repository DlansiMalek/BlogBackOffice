<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableReponseValue extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Reponse_Value', function (Blueprint $table) {
            $table->increments('reponse_value_id');

            $table->unsignedInteger('form_input_reponse_id');
            $table->unsignedInteger('form_input_value_id');
            $table->foreign('form_input_reponse_id')->references('form_input_reponse_id')->on('Form_Input_Reponse');
            $table->foreign('form_input_value_id')->references('form_input_value_id')->on('Form_Input_Value');

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

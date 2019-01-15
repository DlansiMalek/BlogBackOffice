<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableFormInputValue extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Form_Input_Value', function (Blueprint $table) {
            $table->increments('form_input_value_id');
            $table->string("value");
            $table->unsignedInteger("form_input_id");
            $table->foreign("form_input_id")->references("form_input_id")->on("form_input");
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

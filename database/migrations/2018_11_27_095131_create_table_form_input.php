<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableFormInput extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Form_Input', function (Blueprint $table){
            $table->increments("form_input_id");
            $table->string("label");
            $table->unsignedInteger("congress_id");
            $table->unsignedInteger('type_id');
            $table->foreign('congress_id')->references("congress_id")->on("Congress");
            $table->foreign('type_id')->references("type_id")->on("Form_Input_Type");
            $table->timestamps();
        });
        //
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

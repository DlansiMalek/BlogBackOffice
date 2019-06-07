<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFormInputTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Form_Input', function (Blueprint $table) {
            $table->increments("form_input_id");
            $table->string("label");
            $table->unsignedInteger("congress_id");
            $table->unsignedInteger('form_input_type_id');
            $table->foreign('congress_id')->references("congress_id")->on("Congress");
            $table->foreign('form_input_type_id')->references("form_input_type_id")->on("Form_Input_Type");

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
        Schema::dropIfExists('form_input');
    }
}

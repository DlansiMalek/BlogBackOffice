<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFormInputValueTable extends Migration
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
            $table->foreign("form_input_id")->references("form_input_id")->on("Form_Input")->onDelete('cascade');

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
        Schema::table('Form_Input_Value', function (Blueprint $table) {
            $table->dropForeign(['form_input_id']);
        });
        Schema::dropIfExists('Form_Input_Value');
    }
}

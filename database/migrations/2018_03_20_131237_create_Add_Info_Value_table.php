<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAddInfoValueTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Add_Info_Value', function (Blueprint $table) {
            $table->increments('add_info_value_id');
            $table->string('value');


            $table->integer('add_info_id')->unsigned();
            $table->foreign('add_info_id')->references('add_info_id')->on('Add_Info')
                ->onDelete('cascade');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('user_id')->on('User')
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
        Schema::dropIfExists('Add_Info_Value');
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAddInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Add_Info', function (Blueprint $table) {
            $table->increments('add_info_id');

            $table->integer('type_info_id')->unsigned();
            $table->foreign('type_info_id')->references('type_info_id')->on('Type_Info')
                ->onDelete('cascade');

            $table->integer('congress_id')->unsigned();
            $table->foreign('congress_id')->references('congress_id')->on('Congress')
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
        Schema::dropIfExists('Add_Info');
    }
}

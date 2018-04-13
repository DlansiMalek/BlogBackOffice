<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccessTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Access', function (Blueprint $table) {
            $table->increments('access_id');
            $table->double('price');

            $table->integer('type_access_id')->unsigned();
            $table->foreign('type_access_id')->references('type_access_id')->on('Type_Access')
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
        Schema::dropIfExists('Access');
    }
}

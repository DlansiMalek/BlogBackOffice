<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateResourcesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Resource', function (Blueprint $table) {
            $table->softDeletes();

            $table->increments('resource_id');

            //access
            $table->integer('access_id')->unsigned();
            $table->foreign('access_id')->references('access_id')->on('Access')
                ->onDelete('cascade');

            $table->string('name');
            $table->string('path');


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
        Schema::dropIfExists('Resource');
    }
}

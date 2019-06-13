<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateResourceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Resource', function (Blueprint $table) {
            $table->increments('resource_id');
            $table->string('path');

            $table->unsignedInteger('access_id')->nullable()->default(null);
            $table->foreign('access_id')->references('access_id')->on('Access');
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
        Schema::dropIfExists('Resource');
    }
}

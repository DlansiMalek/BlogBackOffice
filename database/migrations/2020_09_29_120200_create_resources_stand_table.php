<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateResourcesStandTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Resources_stand', function (Blueprint $table) {
            $table->increments('id_resources_stand');
            $table->unsignedInteger('resource_id');
            $table->foreign('resource_id')->references('resource_id')->on('Resource')
                ->onDelete('cascade');
                
            $table->unsignedInteger('stand_id');
            $table->foreign('stand_id')->references('stand_id')->on('Stands')
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
        Schema::dropIfExists('Resources_stand');
    }
}

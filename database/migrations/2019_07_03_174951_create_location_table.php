<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLocationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Location', function (Blueprint $table) {
            $table->increments('location_id');

            $table->double('lng');
            $table->double('lat');
            $table->string('adress');

            $table->integer('congress_id')->unsigned();
            $table->foreign('congress_id')->references('congress_id')
                ->on('Congress')->onDelete('cascade');


            $table->integer('city_id')->unsigned()->nullable();
            $table->foreign('city_id')->references('city_id')
                ->on('City')->onDelete('set null');


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
        Schema::dropIfExists('location');
    }
}

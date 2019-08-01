<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('City', function (Blueprint $table) {
            $table->bigIncrements('city_id');

            $table->string('name');
            $table->string('country_code');
            $table->string('name_arabe')->nullable();

            $table->foreign('country_code')
                ->references('alpha3code')->on('Country');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('city');
    }
}

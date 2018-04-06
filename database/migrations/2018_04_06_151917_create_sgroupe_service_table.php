<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSgroupeServiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('SGroupe_Service', function (Blueprint $table) {
            $table->increments('sgroupe_service_id');

            $table->integer('s_groupe_id')->unsigned();
            $table->foreign('s_groupe_id')->references('s_groupe_id')->on('S_Groupe');

            $table->integer('service_id')->unsigned();
            $table->foreign('service_id')->references('service_id')->on('Service');

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
        Schema::dropIfExists('SGroupe_Service');
    }
}

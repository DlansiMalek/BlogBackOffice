<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSperiodeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('S_Periode', function (Blueprint $table) {
            $table->increments('s_periode_id');


            $table->date('start_date');
            $table->date('end_date');

            $table->integer('periode_id')->unsigned();
            $table->foreign('periode_id')->references('periode_id')->on('Periode');

            $table->integer('s_groupe_id')->unsigned();
            $table->foreign('s_groupe_id')->references('s_groupe_id')->on('S_Groupe');

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
        Schema::dropIfExists('S_Periode');
    }
}

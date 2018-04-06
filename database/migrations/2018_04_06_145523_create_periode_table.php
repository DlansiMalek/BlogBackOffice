<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePeriodeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Periode', function (Blueprint $table) {
            $table->increments('periode_id');

            $table->date('start_date');
            $table->date('end_date');

            $table->date('end_middle_date')->nullable();
            $table->date('start_middle_date')->nullable();

            $table->integer('session_stage_id')->unsigned();
            $table->foreign('session_stage_id')->references('session_stage_id')->on('Session_Stage');

            $table->timestamps();
            $table->softDeletes();


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Periode');
    }
}

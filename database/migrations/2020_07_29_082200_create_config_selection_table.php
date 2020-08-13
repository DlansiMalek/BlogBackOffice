<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConfigSelectionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Config_Selection', function (Blueprint $table) {
            $table->bigIncrements('config_selection_id');
            $table->unsignedInteger('congress_id')->unique();
            $table->foreign('congress_id')->references('congress_id')->on('Congress');
            $table->integer('num_evaluators');
            $table->tinyInteger('selection_type')->nullable()->default(0);
            $table->dateTime('start_date');
            $table->dateTime('end_date');
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
        Schema::dropIfExists('Config_Selection');
    }
}

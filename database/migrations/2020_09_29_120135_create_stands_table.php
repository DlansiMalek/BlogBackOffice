<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStandsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Stands', function (Blueprint $table) {
            $table->increments('stand_id');
            $table->string('name');
            $table->unsignedInteger('organization_id');
            $table->foreign("organization_id")
                ->references('organization_id')
                ->on('Organization');


            $table->unsignedInteger('congress_id');
            $table->foreign("congress_id")
                ->references('congress_id')
                ->on('Congress');
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
        Schema::dropIfExists('Stands');
    }
}

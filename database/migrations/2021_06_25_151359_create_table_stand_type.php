<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableStandType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Stand_Type', function (Blueprint $table) {
            $table->increments('stand_type_id');

            $table->string('name');
            $table->string('preview_img')->nullable()->default(null);
            $table->boolean('is_fixed')->nullable()->default(false);
            $table->boolean('is_publicity')->nullable()->default(false);
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
        Schema::dropIfExists('Stand_Type');
    }
}

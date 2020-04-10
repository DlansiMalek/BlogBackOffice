<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateadminThemeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Theme_Admin', function (Blueprint $table) {
            $table->increments('theme_admin_id');
            $table->unsignedInteger('theme_id');

            $table->foreign('theme_id')->references('theme_id')->on('Theme')
                ->onDelete('cascade');

            $table->unsignedInteger('admin_id');
            $table->foreign('admin_id')->references('admin_id')->on('Admin')
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
        Schema::dropIfExists('Theme_Admin');
    }
}

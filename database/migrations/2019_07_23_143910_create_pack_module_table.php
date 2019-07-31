<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePackModuleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('packadmin_module', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('pack_id');
            $table->unsignedInteger('module_id');
            $table->foreign('pack_id')->references('pack_id')->on('pack_admin') ->onDelete('cascade');
            $table->foreign('module_id')->references('module_id')->on('module') ->onDelete('cascade');
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
        Schema::dropIfExists('pack_module');
    }
}

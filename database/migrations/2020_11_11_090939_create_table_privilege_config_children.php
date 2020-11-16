<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTablePrivilegeConfigChildren extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Privilege_Config_Children', function (Blueprint $table) {
            $table->increments('privilege_config_children_id');

            $table->unsignedInteger('menu_children_id');
            $table->foreign('menu_children_id')->references('menu_children_id')
                ->on('Menu_Children')->onDelete('cascade');

            $table->unsignedInteger('privilege_config_id');
            $table->foreign('privilege_config_id')->references('privilege_config_id')
                ->on('Privilege_Config')->onDelete('cascade');

            $table->unsignedInteger('menu_id');
            $table->foreign('menu_id')->references('menu_id')
                ->on('Menu')->onDelete('cascade');

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
        Schema::table('Privilege_Config_Children', function (Blueprint $table) {
            $table->dropForeign(['menu_children_id']);
            $table->dropForeign(['privilege_config_id']);
            $table->dropForeign(['menu_id']);
        });
        Schema::dropIfExists('Privilege_Config_Children');
    }
}

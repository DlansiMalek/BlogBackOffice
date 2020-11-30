<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTablePrivilegeMenuChildren extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Privilege_Menu_Children', function (Blueprint $table) {
            $table->increments('privilege_menu_children_id');

            $table->unsignedInteger('menu_children_id')->nullable()->default(null);
            $table->foreign('menu_children_id')->references('menu_children_id')
                ->on('Menu_Children')->onDelete('cascade');

            $table->unsignedInteger('privilege_id');
            $table->foreign('privilege_id')->references('privilege_id')
                ->on('Privilege')->onDelete('cascade');

            $table->unsignedInteger('menu_id');
            $table->foreign('menu_id')->references('menu_id')
                ->on('Menu')->onDelete('cascade');

            $table->unsignedInteger('congress_id');
            $table->foreign('congress_id')->references('congress_id')
                ->on('Congress')->onDelete('cascade');

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
        Schema::table('Privilege_Menu_Children', function (Blueprint $table) {
            $table->dropForeign(['menu_children_id']);
            $table->dropForeign(['privilege_id']);
            $table->dropForeign(['menu_id']);
            $table->dropForeign(['congress_id']);
        });
        Schema::dropIfExists('Privilege_Menu_Children');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableMenuChildrenOffre extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Menu_Children_Offre', function (Blueprint $table) {
            $table->increments('menu_children_offre_id');

            $table->unsignedInteger('menu_children_id');
            $table->foreign('menu_children_id')->references('menu_children_id')
                ->on('Menu_Children')->onDelete('cascade');

            $table->unsignedInteger('offre_id');
            $table->foreign('offre_id')->references('offre_id')
                ->on('Offre')->onDelete('cascade');

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
        Schema::table('Menu_Children_Offre', function (Blueprint $table) {
            $table->dropForeign(['menu_children_id']);
            $table->dropForeign(['offre_id']);
            $table->dropForeign(['menu_id']);
        });
        Schema::dropIfExists('Menu_Children_Offre');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableMenuChildren extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Menu_Children', function (Blueprint $table) {
            $table->increments('menu_children_id');
            $table->string('key');
            $table->string('url');
            $table->string('icon')->nullable()->default(null);
            $table->boolean('reload')->nullable()->default(null);

            $table->unsignedInteger('menu_id');
            $table->foreign('menu_id')->references('menu_id')
                ->on('Menu')
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
        Schema::table('Menu_Children', function (Blueprint $table) {
            $table->dropForeign(['menu_id']);
        });
        Schema::dropIfExists('Menu_Children');
    }
}

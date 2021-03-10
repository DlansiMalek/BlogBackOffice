<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableAccessGame extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Access_Game', function (Blueprint $table) {
            $table->increments('access_game_id');
            $table->integer('score');

            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('user_id')
                ->on('User')
                ->onDelete('cascade');

            $table->unsignedInteger('access_id');
            $table->foreign('access_id')->references('access_id')
                ->on('Access')
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
        Schema::table('Access_Game', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['access_id']);
        });
        Schema::dropIfExists('Access_Game');
    }
}

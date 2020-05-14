<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserPackTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('User_Pack', function (Blueprint $table) {
            $table->increments('user_pack_id');

            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('user_id')->on('User')->onDelete('cascade');

            $table->unsignedInteger('pack_id');
            $table->foreign('pack_id')->references('pack_id')->on('Pack')->onDelete('cascade');

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
        Schema::table('User_Pack', function (Blueprint $table) {
            $table->dropForeign(['pack_id']);
            $table->dropForeign(['user_id']);
        });
        Schema::dropIfExists('User_Pack');
    }
}

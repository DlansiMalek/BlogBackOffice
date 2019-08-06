<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccessChairTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Access_Chair', function (Blueprint $table) {
            $table->increments('access_chair_id');

            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('user_id')->on('User')->onDelete('cascade');

            $table->unsignedInteger('access_id');
            $table->foreign('access_id')->references('access_id')->on('Access')->onDelete('cascade');

            $table->softDeletes();
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
        Schema::table('Access_Chair', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['access_id']);
        });
        Schema::dropIfExists('Access_Chair');
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccessVoteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Access_Vote', function (Blueprint $table) {
            $table->increments('access_vote_id');
            $table->integer('access_id')->unsigned();
            $table->integer('vote_id')->unsigned();
            $table->foreign('access_id')->references('access_id')->on("Access");
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
        Schema::dropIfExists('access_vote');
    }
}

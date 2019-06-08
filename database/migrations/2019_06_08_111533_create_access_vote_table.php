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
            $table->string('vote_id');

            $table->unsignedInteger('access_id');
            $table->foreign('access_id')->references('access_id')->on('Access')->nullable()->onDelete('cascade');

            $table->unsignedInteger('congress_id');
            $table->foreign('congress_id')->references('congress_id')->on('Congress')->onDelete('cascade');


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

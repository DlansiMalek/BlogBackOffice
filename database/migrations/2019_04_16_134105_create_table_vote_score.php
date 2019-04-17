<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableVoteScore extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Vote_Score', function (Blueprint $table) {
            $table->increments('vote_score_id');
            $table->integer('user_id')->unsigned();
            $table->integer('access_vote_id')->unsigned();
            $table->integer('score');

            $table->foreign('user_id')->references('user_id')->on("User");
            $table->foreign('access_vote_id')->references('access_vote_id')->on("Access_Vote");
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
        //
    }
}

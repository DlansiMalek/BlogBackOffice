<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVoteScoreTable extends Migration
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
            $table->double('score');
            $table->unsignedInteger('num_user_vote');

            $table->unsignedInteger('access_vote_id');
            $table->foreign('access_vote_id')->references('access_vote_id')->on('Access_Vote');

            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('user_id')->on('User')->nullable()->onDelete('cascade');

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
        Schema::dropIfExists('vote_score');
    }
}

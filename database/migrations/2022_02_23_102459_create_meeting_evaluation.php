<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMeetingEvaluation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Meeting_Evaluation', function (Blueprint $table) {
            $table->increments('meeting_evaluation_id');
            $table->unsignedInteger('meeting_id');
            $table->foreign('meeting_id')->references('meeting_id')->on('Meeting')->onDelete('cascade');
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('user_id')->on('User')->onDelete('cascade');
            $table->string('comment')->nullable()->default(null);
            $table->tinyInteger('note')->default(0);
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
        Schema::table('Meeting_Evaluation', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['meeting_id']);
        });
        Schema::dropIfExists('Meeting_Evaluation');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableUserMeeting extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('User_Meeting', function (Blueprint $table) {
            $table->increments('user_meeting_id');
            $table->tinyInteger('status')->default(0);
            $table->unsignedInteger('user_sender_id');
            $table->foreign('user_sender_id')->references('user_id')->on('User')->onDelete('cascade');
            $table->unsignedInteger('user_canceler')->nullable();
            $table->foreign('user_canceler')->references('user_id')->on('User')->onDelete('cascade');
            $table->unsignedInteger('user_receiver_id');
            $table->foreign('user_receiver_id')->references('user_id')->on('User')->onDelete('cascade');
            $table->unsignedInteger('meeting_id');
            $table->foreign('meeting_id')->references('meeting_id')->on('Meeting')->onDelete('cascade');
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
        Schema::table('User_Meeting', function (Blueprint $table) {
            $table->dropForeign(['user_sender_id']);
            $table->dropForeign(['user_receiver_id']);
            $table->dropForeign(['meeting_id']);
            $table->dropForeign(['user_canceler']);
        });
        Schema::dropIfExists('User_Meeting');
    }
}

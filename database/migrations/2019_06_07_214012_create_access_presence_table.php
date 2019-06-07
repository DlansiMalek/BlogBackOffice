<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccessPresenceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Access_Presence', function (Blueprint $table) {
            $table->increments('access_presence_id');
            $table->dateTime('entered_at');
            $table->dateTime('left_at');

            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('user_id')->on('User')->onDelete('cascade');

            $table->unsignedInteger('access_id');
            $table->foreign('access_id')->references('access_id')->on('Access')->onDelete('cascade');

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
        Schema::dropIfExists('access_presence');
    }
}

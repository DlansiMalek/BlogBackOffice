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
            $table->dateTime('left_at')->nullable()->default(null);

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
        Schema::table('Access_Presence', function (Blueprint $table) {
            $table->dropForeign(['access_id']);
            $table->dropForeign(['user_id']);
        });
        Schema::dropIfExists('Access_Presence');
    }
}

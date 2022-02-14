<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnMeetinIfToTableUserMeeting extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('User_Mail', function (Blueprint $table) {
            $table->unsignedInteger('meeting_id')->nullable()->default(null);
            $table->foreign('meeting_id')->references('meeting_id')
                ->on('Meeting')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('User_Mail', function (Blueprint $table) {
            $table->dropForeign('meeting_id');
            $table->removeColumn('meeting_id');
        });
    }
}

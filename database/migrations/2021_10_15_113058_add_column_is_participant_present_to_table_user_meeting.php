<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnIsParticipantPresentToTableUserMeeting extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('User_Meeting', function (Blueprint $table) {
            $table->tinyInteger('is_participant_present')->nullable()->default(0);
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
            $table->removeColumn('is_participant_present');
        });
    }
}

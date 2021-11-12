<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnMeetingTableIdToMeetingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Meeting', function (Blueprint $table) {
            $table->unsignedInteger('meeting_table_id')->nullable()->default(null);
            $table->foreign('meeting_table_id')->references('meeting_table_id')->on('Meeting_Table');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Meeting', function (Blueprint $table) {
            $table->dropForeign(['meeting_table_id']);
            $table->removeColumn('meeting_table_id');
        });
    }
}

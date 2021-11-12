<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnMeetingTableNumberToConfigCongressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Config_Congress', function (Blueprint $table) {
            $table->integer('nb_meeting_table')->default(0)->nullable();;
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Config_Congress', function (Blueprint $table) {
            $table->removeColumn("nb_meeting_table");
        });
    }
}

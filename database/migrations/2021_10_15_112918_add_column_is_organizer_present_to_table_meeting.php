<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnIsOrganizerPresentToTableMeeting extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Meeting', function (Blueprint $table) {
            $table->tinyInteger('is_organizer_present')->nullable()->default(0);
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
            $table->removeColumn('is_organizer_present');
        });
    }
}

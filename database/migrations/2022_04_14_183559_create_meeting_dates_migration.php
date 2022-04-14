<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMeetingDatesMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Meeting_Dates', function (Blueprint $table) {
            $table->increments('meeting_dates_id');
            $table->dateTime("start_date")->nullable()->default(null);
            $table->dateTime("end_date")->nullable()->default(null);
            $table->unsignedInteger("congress_id");
            $table->foreign("congress_id")->references('congress_id')->on('Congress')
                ->onDelete('cascade');
        
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
        Schema::table('Meeting_Dates', function (Blueprint $table) { 
            $table->dropForeign('congress_id');
         });
         Schema::dropIfExists('Meeting_Dates');
    }
}

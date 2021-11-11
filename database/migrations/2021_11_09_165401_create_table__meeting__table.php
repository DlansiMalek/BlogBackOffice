<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableMeetingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Meeting_Table', function (Blueprint $table) {
            $table->increments('meeting_table_id');
            $table->string('label');
            $table->unsignedInteger('congress_id')->nullable()->default(null);
            $table->foreign('congress_id')->references('congress_id')->on('Congress')->onDelete('cascade');
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
        Schema::table('Meeting_Table', function (Blueprint $table) {
            $table->dropForeign(['congress_id']);
        });
        Schema::dropIfExists('Meeting_Table');
    }
}

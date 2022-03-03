<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserIdBannerToMeetingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Meeting_Table', function (Blueprint $table) {
            $table->unsignedInteger('user_id')->nullable()->default(null);;
            $table->foreign('user_id')->references('user_id')
                ->on('User')->onDelete('cascade');

            $table->string('banner')->nullable()->default(null);
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
            $table->dropForeign('user_id');
            $table->removeColumn('banner');
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAccessIdToAllowedOnlineAccess extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Allowed_Online_Access', function (Blueprint $table) {
            $table->unsignedInteger('access_id')->unsigned()->nullable()->default(null);
            $table->foreign('access_id')->references('access_id')->on('Access')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Allowed_Online_Access', function (Blueprint $table) {
            $table->dropForeign(['access_id']);

        });
    }
}

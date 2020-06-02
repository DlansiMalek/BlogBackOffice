<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdminIdToCustomSms extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Custom_SMS', function (Blueprint $table) {
            $table->Integer('admin_id')->after('custom_sms_id')->unsigned();
            $table->foreign('admin_id')->references('admin_id')->on('Admin')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Custom_SMS', function (Blueprint $table) {
            $table->dropForeign(['admin_id']);
        });
    }

}

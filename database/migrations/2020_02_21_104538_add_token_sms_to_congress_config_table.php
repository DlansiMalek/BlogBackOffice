<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTokenSmsToCongressConfigTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Config_Congress', function (Blueprint $table) {
            $table->boolean("is_notif_sms_confirm")->default(0)
                ->after('is_notif_register_mail');
            $table->string('token_sms')->nullable()->default(null)
                ->after('is_notif_sms_confirm');
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
            $table->dropColumn('token_sms');
            $table->dropColumn('is_notif_sms_confirm');
        });
    }
}

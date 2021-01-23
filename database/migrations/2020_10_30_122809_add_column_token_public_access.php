<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnTokenPublicAccess extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Access', function (Blueprint $table) {
            $table->text('token_jitsi_moderator')->nullable()->default(null)->after('token_jitsi');
            $table->text('token_jitsi_participant')->nullable()->default(null)->after('token_jitsi');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Access', function (Blueprint $table) {
            $table->dropColumn(['token_jitsi_moderator']);
            $table->dropColumn(['token_jitsi_participant']);
        });
    }
}

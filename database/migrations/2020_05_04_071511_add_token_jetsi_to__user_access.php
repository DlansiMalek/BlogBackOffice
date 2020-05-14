<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTokenJetsiToUserAccess extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('User_Access', function (Blueprint $table) {
            $table->text('token_jitsi')->nullable()->default(null)->after('access_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('User_Access', function (Blueprint $table) {
            $table->dropColumn('token_jitsi');
        });
    }
}

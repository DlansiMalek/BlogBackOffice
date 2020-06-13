<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTokenJetsiToUserCongressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('User_Congress', function (Blueprint $table) {
            $table->text('token_jitsi')->nullable()->default(null)->after('congress_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('User_Congress', function (Blueprint $table) {
            $table->dropColumn(['token_jitsi']);
        });
    }
}

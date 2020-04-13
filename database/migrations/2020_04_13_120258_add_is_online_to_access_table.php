<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsOnlineToAccessTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Access', function (Blueprint $table) {
            $table->unsignedTinyInteger('is_online')->default(0)->after('with_attestation');
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
            $table->dropColumn(['is_online']);
        });
    }
}

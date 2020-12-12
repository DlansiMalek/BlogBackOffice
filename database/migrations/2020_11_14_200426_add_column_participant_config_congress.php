<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnParticipantConfigCongress extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Config_Congress', function (Blueprint $table) {
            $table->tinyInteger('nb_current_participants')->nullable()->default(0);
            $table->tinyInteger('max_online_participants')->nullable()->default(0);
            $table->text("url_streaming")->nullable()->default(null);
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
            $table->removeColumn('nb_current_participants');
            $table->removeColumn('max_online_participants');
            $table->removeColumn('url_streaming');
        });
    }
}

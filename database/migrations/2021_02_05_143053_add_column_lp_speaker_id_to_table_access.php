<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnLpSpeakerIdToTableAccess extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Access', function (Blueprint $table) {
            $table->unsignedInteger('lp_speaker_id')->nullable()->default(null);
            $table->foreign('lp_speaker_id')->references('lp_speaker_id')
                ->on('LP_Speaker')->onDelete('set null');
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
            $table->dropForeign(['lp_speaker_id']);
            $table->removeColumn("lp_speaker_id");
        });
    }
}

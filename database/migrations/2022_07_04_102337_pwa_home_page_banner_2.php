<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PwaHomePageBanner2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Config_LP', function (Blueprint $table) {
            $table->string('home_banner_event_en')->nullable()->default(null);
            $table->string('home_banner_event_ar')->nullable()->default(null);
            $table->string('prp_banner_event_en')->nullable()->default(null);
            $table->string('prp_banner_event_ar')->nullable()->default(null);
            $table->string('specific_bnr_en')->nullable()->default(null);
            $table->string('specific_bnr_two_en')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Config_LP', function (Blueprint $table) {
            $table->removeColumn('home_banner_event_en');
            $table->removeColumn('home_banner_event_ar');
            $table->removeColumn('prp_banner_event_en');
            $table->removeColumn('prp_banner_event_ar');
            $table->removeColumn('specific_bnr_en');
            $table->removeColumn('specific_bnr_two_en');
        });
    }
}

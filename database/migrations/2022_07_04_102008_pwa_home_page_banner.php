<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PwaHomePageBanner extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Config_Congress', function (Blueprint $table) {
            $table->string('banner_ar')->nullable()->default(null);
            $table->string('banner_en')->nullable()->default(null);
            $table->string('logo_en')->nullable()->default(null);
            $table->string('logo_ar')->nullable()->default(null);
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
            $table->removeColumn('banner_ar');
            $table->removeColumn('banner_en');
            $table->removeColumn('logo_en');
            $table->removeColumn('logo_ar');
        });
    }
}

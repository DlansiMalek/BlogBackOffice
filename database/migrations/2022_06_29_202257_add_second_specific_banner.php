<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSecondSpecificBanner extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Config_LP', function (Blueprint $table) {
            $table->string('specific_bnr_two')->nullable()->default(null);
            $table->string('specific_bnr_two_title')->nullable()->default(null);
            $table->text('specific_bnr_two_description')->nullable()->default(null);
            $table->string('specific_bnr_two_title_en')->nullable()->default(null);
            $table->text('specific_bnr_two_description_en')->nullable()->default(null);
            $table->string('specific_bnr_two_ar')->nullable()->default(null);
            $table->string('specific_bnr_two_title_ar')->nullable()->default(null);
            $table->text('specific_bnr_two_description_ar')->nullable()->default(null);
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
            $table->removeColumn('specific_bnr_two');
            $table->removeColumn('specific_bnr_two_title');
            $table->removeColumn('specific_bnr_two_description');
            $table->removeColumn('specific_bnr_two_title_en');
            $table->removeColumn('specific_bnr_two_description_en');
            $table->removeColumn('specific_bnr_two_ar');
            $table->removeColumn('specific_bnr_two_title_ar');
            $table->removeColumn('specific_bnr_two_description_ar');
        });
    }
}

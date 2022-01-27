<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsPartnersToConfigLp extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Config_LP', function (Blueprint $table) {
            $table->string('partners_title')->nullable()->default(null);
            $table->text('partners_description')->nullable()->default(null);
            $table->string('partners_title_en')->nullable()->default(null);
            $table->text('partners_description_en')->nullable()->default(null);
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
            $table->removeColumn('partners_title');
            $table->removeColumn('partners_description');
            $table->removeColumn('partners_title_en');
            $table->removeColumn('partners_description_en');
        });
    }
}

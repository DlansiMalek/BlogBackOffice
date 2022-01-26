<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnHomeSubTitleToTableConfigCongress extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Config_LP', function (Blueprint $table) {
            $table->string('home_sub_title')->nullable()->default(null);
            $table->string('home_sub_title_en')->nullable()->default(null);
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
            $table->removeColumn('home_sub_title');
            $table->removeColumn('home_sub_title_en');
        });
    }
}

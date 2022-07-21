<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPageTitleArAndLiveLinkArColumnsToConfigLPTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Config_LP', function (Blueprint $table) {
            $table->string('page_title_ar')->nullable()->default(null);
            $table->string('live_link_ar')->nullable()->default(null);
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
            $table->removeColumn('page_title_ar');
            $table->removeColumn('live_link_ar');
        });
    }
}

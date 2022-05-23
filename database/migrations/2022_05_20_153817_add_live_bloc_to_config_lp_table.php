<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLiveBlocToConfigLpTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Config_LP', function (Blueprint $table) {
            $table->text('live_title_btn')->nullable()->default(null);
            $table->text('live_title_btn_en')->nullable()->default(null);
            $table->text('live_title')->nullable()->default(null);
            $table->text('live_title_en')->nullable()->default(null);
            $table->text('live_link')->nullable()->default(null);
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
            $table->removeColumn('live_link');
            $table->removeColumn('live_title_en');
            $table->removeColumn('live_title');
            $table->removeColumn('live_title_btn_en');
            $table->removeColumn('live_title_btn');
        });
    }
}

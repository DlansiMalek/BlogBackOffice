<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToConfigLPTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Config_LP', function (Blueprint $table) {
            $table->string('waiting_title')->nullable()->default(null);
            $table->text('waiting_desription')->nullable()->default(null);
            $table->string('waiting_title_en')->nullable()->default(null);
            $table->text('waiting_desription_en')->nullable()->default(null);
            $table->dateTime('opening_date')->nullable()->default(null);
            $table->string('waiting_banner')->nullable()->default(null);
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
            $table->removeColumn('waiting_title');
            $table->removeColumn('waiting_desription');
            $table->removeColumn('waiting_title_en');
            $table->removeColumn('waiting_desription_en');
            $table->removeColumn('opening_date');
            $table->removeColumn('waiting_banner');
        });
    }
}

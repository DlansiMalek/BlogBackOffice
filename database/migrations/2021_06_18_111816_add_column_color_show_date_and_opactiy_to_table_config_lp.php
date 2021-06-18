<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnColorShowDateAndOpactiyToTableConfigLp extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Config_LP', function (Blueprint $table) {
            $table->unsignedTinyInteger('show_date')->default(0);
            $table->string('background_color')->nullable()->default(null);
            $table->double('opacity_color')->nullable()->default(null);
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

            $table->removeColumn('show_date');
            $table->removeColumn('background_color');
            $table->removeColumn('opacity_color');
        });
    }
}

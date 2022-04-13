<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFixtableCounterConfigurationToConfigCongressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Config_Congress', function (Blueprint $table) {
            $table->tinyInteger('nb_fix_table')->nullable()->default(null);
            $table->string('label_fix_table')->nullable()->default(null);
            $table->string('label_meeting_table')->nullable()->default(null);
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
            $table->removeColumn('nb_fix_table');
            $table->removeColumn('label_fix_table');
            $table->removeColumn('label_meeting_table');
            
        });
    }
}

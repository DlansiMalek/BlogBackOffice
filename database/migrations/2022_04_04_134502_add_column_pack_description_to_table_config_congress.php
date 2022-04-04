<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnPackDescriptionToTableConfigCongress extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Config_Congress', function (Blueprint $table) {
            $table->text('pack_description')->nullable()->default(null);
            $table->text('pack_description_en')->nullable()->default(null);
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
            $table->removeColumn('pack_description');
            $table->removeColumn('pack_description_en');
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnOrganizersTitleToTableConfigLp extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Config_LP', function (Blueprint $table) {
            $table->string('organizers_title')->nullable()->default(null);
            $table->text('organizers_description')->nullable()->default(null);
            $table->string('organizers_title_en')->nullable()->default(null);
            $table->text('organizers_description_en')->nullable()->default(null);
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
            $table->removeColumn('organizers_title');
            $table->removeColumn('organizers_description');
            $table->removeColumn('organizers_title_en');
            $table->removeColumn('organizers_description_en');
        });
    }
}

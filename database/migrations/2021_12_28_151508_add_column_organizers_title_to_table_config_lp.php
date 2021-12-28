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
            $table->string('organizers_title');
            $table->text('organizers_description');
            $table->string('organizers_title_en');
            $table->text('organizers_description_en');
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

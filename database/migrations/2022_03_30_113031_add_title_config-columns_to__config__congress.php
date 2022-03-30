<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTitleConfigColumnsToConfigCongress extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Config_Congress', function (Blueprint $table) {
            $table->string("pack_title")->default("Choisir un pack");
            $table->string("pack_title_en")->default("Choose a pack");
            $table->string("access_title")->default("Veuillez sélectionner un (des) ticket(s)");
            $table->string("access_title_en")->default("Choose Ticket");
            $table->string("prise_charge_title")->default("Prise en charge");
            $table->string("prise_charge_title_en")->default("Supported by");
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
            $table->removeColumn("pack_title");
            $table->removeColumn("pack_title_en");
            $table->removeColumn("access_title");
            $table->removeColumn("access_title_en");
            $table->removeColumn("prise_charge_title");
            $table->removeColumn("prise_charge_title_en");
        });
    }
}

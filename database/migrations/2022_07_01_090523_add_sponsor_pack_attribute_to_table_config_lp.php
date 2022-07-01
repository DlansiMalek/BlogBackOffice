<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSponsorPackAttributeToTableConfigLp extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Config_LP', function (Blueprint $table) {
            $table->string('sponsor_pack_title')->nullable()->default(null);
            $table->text('sponsor_pack_description')->nullable()->default(null);
            $table->string('sponsor_pack_title_en')->nullable()->default(null);
            $table->text('sponsor_pack_description_en')->nullable()->default(null);
            $table->string('sponsor_pack_title_ar')->nullable()->default(null);
            $table->text('sponsor_pack_description_ar')->nullable()->default(null);
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
            $table->removeColumn('sponsor_pack_title');
            $table->removeColumn('sponsor_pack_description');
            $table->removeColumn('sponsor_pack_title_en');
            $table->removeColumn('sponsor_pack_description_en');
            $table->removeColumn('sponsor_pack_title_ar');
            $table->removeColumn('sponsor_pack_description_ar');
        });
    }
}

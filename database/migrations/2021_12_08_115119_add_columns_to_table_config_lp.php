<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToTableConfigLp extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Config_LP', function (Blueprint $table) {
            $table->string('home_title_en')->nullable()->default(null);
            $table->text('home_description_en')->nullable()->default(null);

            $table->string('prp_title_en')->nullable()->default(null);
            $table->text('prp_description_en')->nullable()->default(null);

            $table->string('speaker_title_en')->nullable()->default(null);
            $table->text('speaker_description_en')->nullable()->default(null);

            $table->string('sponsor_title_en')->nullable()->default(null);
            $table->text('sponsor_description_en')->nullable()->default(null);
            
            $table->string('prg_title_en')->nullable()->default(null);
            $table->text('prg_description_en')->nullable()->default(null);
             
            $table->string('contact_title_en')->nullable()->default(null);
            $table->text('contact_description_en')->nullable()->default(null);

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
            $table->removeColumn('home_title_en');
            $table->removeColumn('home_description_en');
            $table->removeColumn('prp_title_en');
            $table->removeColumn('prp_description_en');
            $table->removeColumn('speaker_title_en');
            $table->removeColumn('speaker_description_en');
            $table->removeColumn('sponsor_title_en');
            $table->removeColumn('sponsor_description_en');
            $table->removeColumn('prg_title_en');
            $table->removeColumn('prg_description_en');
            $table->removeColumn('contact_title_en');
            $table->removeColumn('contact_description_en');
        });
    }
}

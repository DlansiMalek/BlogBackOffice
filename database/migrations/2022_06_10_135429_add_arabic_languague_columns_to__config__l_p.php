<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddArabicLanguagueColumnsToConfigLP extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Config_LP', function (Blueprint $table) {
            $table->string('home_title_ar')->nullable()->default(null);
            $table->string('home_description_ar')->nullable()->default(null);
            $table->string('prp_title_ar')->nullable()->default(null);
            $table->string('prp_description_ar')->nullable()->default(null);
            $table->string('speaker_title_ar')->nullable()->default(null);
            $table->string('speaker_description_ar')->nullable()->default(null);
            $table->string('sponsor_title_ar')->nullable()->default(null);
            $table->string('sponsor_description_ar')->nullable()->default(null);
            $table->string('prg_title_ar')->nullable()->default(null);
            $table->string('prg_description_ar')->nullable()->default(null);
            $table->string('contact_title_ar')->nullable()->default(null);
            $table->string('contact_description_ar')->nullable()->default(null);
            $table->string('home_sub_title_ar')->nullable()->default(null);
            $table->string('organizers_title_ar')->nullable()->default(null);
            $table->string('organizers_description_ar')->nullable()->default(null);
            $table->string('partners_title_ar')->nullable()->default(null);
            $table->string('partners_description_ar')->nullable()->default(null);
            $table->string('waiting_title_ar')->nullable()->default(null);
            $table->string('waiting_description_ar')->nullable()->default(null);
            $table->string('prp_btn_text_ar')->nullable()->default(null);
            $table->string('live_title_btn_ar')->nullable()->default(null);
            $table->string('live_title_ar')->nullable()->default(null);
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
            $table->removeColumn('home_title_ar');
            $table->removeColumn('home_description_ar');
            $table->removeColumn('prp_title_ar');
            $table->removeColumn('prp_description_ar');
            $table->removeColumn('speaker_title_ar');
            $table->removeColumn('speaker_description_ar');
            $table->removeColumn('sponsor_title_ar');
            $table->removeColumn('sponsor_description_ar');
            $table->removeColumn('prg_title_ar');
            $table->removeColumn('prg_description_ar');
            $table->removeColumn('contact_title_ar');
            $table->removeColumn('contact_description_ar');
            $table->removeColumn('home_sub_title_ar');
            $table->removeColumn('organizers_title_ar');
            $table->removeColumn('organizers_description_ar');
            $table->removeColumn('partners_title_ar');
            $table->removeColumn('partners_description_ar');
            $table->removeColumn('waiting_title_ar');
            $table->removeColumn('waiting_description_ar');
            $table->removeColumn('prp_btn_text_ar');
            $table->removeColumn('live_title_btn_ar');
            $table->removeColumn('live_title_ar');
        });
    }
}

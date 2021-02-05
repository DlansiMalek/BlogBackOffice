<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableConfigLp extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Config_LP', function (Blueprint $table) {
            $table->increments('config_lp_id');
           
            $table->unsignedInteger('congress_id');
            $table->foreign('congress_id')->references('congress_id')
                  ->on('Congress')->onDelete('cascade');

            
            $table->string('header_logo_event')->nullable()->default(null);
            $table->unsignedTinyInteger('is_inscription')->default(0);
            $table->string('register_link')->nullable()->default(null);

            $table->string('home_banner_event')->nullable()->default(null);
            $table->date('home_start_date')->nullable()->default(null);
            $table->date('home_end_date')->nullable()->default(null);
            $table->string('home_title')->nullable()->default(null);
            $table->string('home_description')->nullable()->default(null);

            $table->string('prp_banner_event')->nullable()->default(null);
            $table->string('prp_title')->nullable()->default(null);
            $table->string('prp_description')->nullable()->default(null);

            $table->string('speaker_title')->nullable()->default(null);
            $table->string('speaker_description')->nullable()->default(null);

            $table->string('sponsor_title')->nullable()->default(null);
            $table->string('sponsor_description')->nullable()->default(null);
            
            $table->string('prg_title')->nullable()->default(null);
            $table->string('prg_description')->nullable()->default(null);
             
            $table->string('contact_title')->nullable()->default(null);
            $table->string('contact_description')->nullable()->default(null);

            $table->string('event_link_fb')->nullable()->default(null);
            $table->string('event_link_instagram')->nullable()->default(null);
            $table->string('event_link_linkedin')->nullable()->default(null);
            $table->string('event_link_twitter')->nullable()->default(null);

            $table->string('theme_color')->nullable()->default(null);
            $table->string('theme_mode')->nullable()->default(null);


            $table->timestamps();
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
            $table->dropForeign(['congress_id']);
        });
        Schema::dropIfExists('Config_LP');
    }
}

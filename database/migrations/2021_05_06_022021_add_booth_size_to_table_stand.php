<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBoothSizeToTableStand extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Stand', function (Blueprint $table) {
            $table->string('booth_size')->nullable()->default(null);
            $table->string('website_link')->nullable()->default(null);
            $table->string('fb_link')->nullable()->default(null);
            $table->string('insta_link')->nullable()->default(null);
            $table->string('twitter_link')->nullable()->default(null);
            $table->string('linkedin_link')->nullable()->default(null);
            $table->integer('priority')->nullable()->default(null);
            $table->string('primary_color')->nullable()->default(null);
            $table->string('secondary_color')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Stand', function (Blueprint $table) {
            $table->removeColumn("booth_size");
            $table->removeColumn("website_link");
            $table->removeColumn("fb_link");
            $table->removeColumn("insta_link");
            $table->removeColumn("twitter_link");
            $table->removeColumn("linkedin_link");
            $table->removeColumn("priority");
            $table->removeColumn("primary_color");
            $table->removeColumn("secondary_color");
        });
    }
}

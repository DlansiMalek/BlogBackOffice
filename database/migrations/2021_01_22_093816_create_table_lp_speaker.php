<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableLpSpeaker extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('LP_Speaker', function (Blueprint $table) {
            $table->increments('lp_speaker_id');
           
            $table->unsignedInteger('congress_id');
            $table->foreign('congress_id')->references('congress_id')
                  ->on('Congress')->onDelete('cascade');
            
            $table->string('first_name');
            $table->string('last_name');
            $table->string('role');
            $table->string('profile_img');
            $table->string('fb_link')->nullable()->default(null);
            $table->string('linkedin_link')->nullable()->default(null);
            $table->string('instagram_link')->nullable()->default(null);
            $table->string('twitter_link')->nullable()->default(null);

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
        Schema::table('LP_Speaker', function (Blueprint $table) {
            $table->dropForeign(['congress_id']);
        });
        Schema::dropIfExists('LP_Speaker');
    }
}

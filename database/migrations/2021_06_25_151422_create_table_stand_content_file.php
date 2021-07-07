<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableStandContentFile extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Stand_Content_File', function (Blueprint $table) {
            $table->increments('stand_content_file_id');
            $table->string('file')->nullable()->default(null);
            $table->string('url')->nullable()->default(null);

            $table->unsignedInteger('stand_id');
            $table->foreign("stand_id")->references('stand_id')->on('Stand')
                ->onDelete('cascade');

            $table->unsignedInteger('stand_content_config_id');
            $table->foreign("stand_content_config_id")->references('stand_content_config_id')->on('Stand_Content_Config')
                ->onDelete('cascade');

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
        Schema::table('Stand_Content_File', function (Blueprint $table) {
            $table->dropForeign(['stand_id']);
            $table->dropForeign(['stand_content_config_id']);
        });
        Schema::dropIfExists('Stand_Content_File');
    }
}

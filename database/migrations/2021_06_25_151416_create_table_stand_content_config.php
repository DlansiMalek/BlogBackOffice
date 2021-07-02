<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableStandContentConfig extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Stand_Content_Config', function (Blueprint $table) {
            $table->increments('stand_content_config_id');

            $table->string('key');
            $table->string('label');
            $table->string('size')->nullable()->default(null);
            $table->string('default_file')->nullable()->default(null);
            $table->string('default_url')->nullable()->default(null);
            $table->string('accept_file')->nullable()->default(null);

            $table->unsignedInteger('stand_type_id');
            $table->foreign("stand_type_id")->references('stand_type_id')->on('Stand_Type')
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
        Schema::table('Stand_Content_Config', function(Blueprint $table) {
            $table->dropForeign(['stand_type_id']);
        });
        Schema::dropIfExists('Stand_Content_Config');
    }
}

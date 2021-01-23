<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccessPackTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Access_Pack', function (Blueprint $table) {
            $table->increments('access_pack_id');

            $table->unsignedInteger('pack_id');
            $table->foreign('pack_id')->references('pack_id')->on('Pack')->onDelete('cascade');

            $table->unsignedInteger('access_id');
            $table->foreign('access_id')->references('access_id')->on('Access')->onDelete('cascade');

            $table->softDeletes();
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
        Schema::table('Access_Pack', function (Blueprint $table) {
            $table->dropForeign(['access_id']);
            $table->dropForeign(['pack_id']);
        });
        Schema::dropIfExists('Access_Pack');
    }
}

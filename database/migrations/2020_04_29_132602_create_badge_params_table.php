<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBadgeParamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Badge_Params', function (Blueprint $table) {
            $table->increments('badge_param_id');
            $table->string("key");
            $table->unsignedInteger('badge_id');
            $table->foreign('badge_id')->references('badge_id')->on('Badge')
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
        Schema::table('Badge_Params', function ($table) {
            $table->dropForeign(['badge_id']);
        });
        Schema::dropIfExists('Badge_Params');

    }
}

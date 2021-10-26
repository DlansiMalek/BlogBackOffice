<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStandTagTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Stand_Tag', function (Blueprint $table) {
            $table->increments("stand_tag_id");

            $table->unsignedInteger("stag_id")->nullable()->default(null);
            $table->foreign("stag_id")->references('stag_id')->on('STag')
                ->onDelete('cascade');

<<<<<<< HEAD
            $table->unsignedInteger("stand_id")->nullable()->default(null);;
=======
            $table->unsignedInteger("stand_id")->nullable()->default(null);
>>>>>>> 5c3c401348d10c5ec82e196f33684e6bef5f2df5
            $table->foreign("stand_id")->references('stand_id')->on('Stand')
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
        Schema::table('Stand_Tag', function (Blueprint $table) {
            $table->dropForeign(['stag_id']);
            $table->dropForeign(['stand_id']);
        });
        Schema::dropIfExists('Stand_Tag');
    }
}

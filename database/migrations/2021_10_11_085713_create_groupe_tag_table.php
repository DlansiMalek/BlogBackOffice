<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGroupeTagTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('GSTag', function (Blueprint $table) {
            $table->increments("gstag_id");
            $table->string('label');
            $table->unsignedInteger("congress_id");
            $table->foreign("congress_id")->references('congress_id')->on('Congress')
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
        Schema::table('GSTag', function (Blueprint $table) { 
            $table->dropForeign('congress_id');
         });
         Schema::dropIfExists('GSTag');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeyToStag extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('STag', function (Blueprint $table) {
            $table->unsignedInteger("gstag_id");
            $table->foreign("gstag_id")->references('gstag_id')->on('GSTag')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('STag', function (Blueprint $table) {
            //
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnStandTypeIdToTableStand extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Stand', function (Blueprint $table) {
            $table->unsignedInteger('stand_type_id')->nullable()->default(null);
            $table->foreign("stand_type_id")->references('stand_type_id')->on('Stand_Type')
                ->onDelete('set null');
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
            $table->dropForeign('stand_type_id');
            $table->removeColumn('stand_type_id');
        });
    }
}

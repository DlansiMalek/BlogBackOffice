<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToTableLpSpeaker extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('LP_Speaker', function (Blueprint $table) {
            $table->string('first_name_en')->nullable()->default(null);
            $table->string('last_name_en')->nullable()->default(null);
            $table->text('role_en')->nullable()->default(null);
            $table->string('first_name_ar')->nullable()->default(null);
            $table->string('last_name_ar')->nullable()->default(null);
            $table->text('role_ar')->nullable()->default(null);
            $table->string('first_name')->nullable()->default(null)->change();
            $table->string('last_name')->nullable()->default(null)->change();
            $table->text('role')->nullable()->default(null)->change();
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
            $table->removeColumn('first_name_en');
            $table->removeColumn('last_name_en');
            $table->removeColumn('role_en');
            $table->removeColumn('first_name_ar');
            $table->removeColumn('last_name_ar');
            $table->removeColumn('role_ar');
            $table->string('first_name')->change();
            $table->string('last_name')->change();
            $table->text('role')->change();
        });
    }
}

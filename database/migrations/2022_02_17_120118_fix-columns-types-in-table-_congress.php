<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixColumnsTypesInTableCongress extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Congress', function (Blueprint $table) {
            $table->string('name_en')->change();
            $table->text('description_en')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Congress', function (Blueprint $table) {
            $table->text('name_en')->change();
            $table->string('description_en')->change();
        });
    }
}

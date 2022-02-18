<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEnglishLabelDescriptionToCongressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Theme', function (Blueprint $table) {
            $table->string('label_en')->nullable()->default(null);
            $table->string('description_en')->nullable()->default(null);
            $table->string('label')->default(null)->nullable()->change();
            $table->string('description')->default(null)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Theme', function (Blueprint $table) {
            $table->removeColumn('label_en');
            $table->removeColumn('description_en');
            $table->string('label')->change();
            $table->string('description')->change();
        });
    }
}

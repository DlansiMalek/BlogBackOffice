<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEnglishNameDescriptionToCongressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Congress', function (Blueprint $table) {
            $table->text('name_en')->nullable()->default(null);
            $table->string('description_en')->nullable()->default(null);
            $table->string('name')->default(null)->nullable()->change();
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
            $table->removeColumn('name_en')->nullable()->default(null);
            $table->removeColumn('description_en')->nullable()->default(null);
            $table->string('name')->change();
        });
    }
}

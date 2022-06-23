<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLabelEnDescriptionEnToPack extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Pack', function (Blueprint $table) {
            $table->string('label_en')->nullable()->default(null);
            $table->text('description_en')->nullable()->default(null);
            $table->string('label')->default(null)->nullable()->change();
            $table->text('description')->default(null)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Pack', function (Blueprint $table) {
            $table->removeColumn('label_en');
            $table->removeColumn('description_en');
            $table->string('label')->change();
            $table->string('description')->change();
        });
    }
}

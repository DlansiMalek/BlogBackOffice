<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeColumnDescriptionTableStandProduct extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Stand_Product', function (Blueprint $table) {
            $table->text('description')->change();
            $table->dropColumn('brochure_file');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Stand_Product', function (Blueprint $table) {
            $table->string('description')->change();
            $table->string('brochure_file')->nullable()->default(null);
        });
    }
}

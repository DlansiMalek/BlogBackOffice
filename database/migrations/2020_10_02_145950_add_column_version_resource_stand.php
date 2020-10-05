<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnVersionResourceStand extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Resource_Stand', function (Blueprint $table) {
            $table->tinyInteger('version')->default(0);
            $table->dropColumn('doc_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Resource_Stand', function (Blueprint $table) {
            $table->dropColumn('version')->default(0);
            $table->string("doc_name")
                ->nullable()->default(null);
        });
    }
}

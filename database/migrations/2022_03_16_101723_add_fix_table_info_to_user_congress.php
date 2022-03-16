<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFixTableInfoToUserCongress extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('User_congress', function (Blueprint $table) {
            $table->string('fix_table_info')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('User_congress', function (Blueprint $table) {
            $table->removeColumn('fix_table_info');
        });
    }
}

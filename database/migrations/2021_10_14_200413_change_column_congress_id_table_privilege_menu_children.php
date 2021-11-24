<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeColumnCongressIdTablePrivilegeMenuChildren extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Privilege_Menu_Children', function (Blueprint $table) {
            $table->unsignedInteger('congress_id')->default(Null)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Privilege_Menu_Children', function (Blueprint $table) {
            $table->unsignedInteger('congress_id')->change();
        });
    }
}

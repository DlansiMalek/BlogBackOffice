<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusValidDateAdminTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Admin', function (Blueprint $table) {
            $table->unsignedTinyInteger('status')->default(1)->after('privilege_id');
            $table->date('valid_date')->nullable()->default(NULL)->after('rfid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Admin', function (Blueprint $table) {
            $table->dropColumn(['status']);
            $table->dropColumn(['valid_date']);
        });
    }
}

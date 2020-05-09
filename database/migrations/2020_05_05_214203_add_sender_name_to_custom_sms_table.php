<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSenderNameToCustomSmsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Custom_SMS', function (Blueprint $table) {
            $table->string('senderName')->nullable()->default('Eventizer')->after('title');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Custom_SMS', function (Blueprint $table) {
            $table->dropColumn('senderName');
        });
    }
}

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
        Schema::table('custom_sms', function (Blueprint $table) {
            $table->string('senderName')->nullable()->default('Ecozone')->after('title');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('custom_sms', function (Blueprint $table) {
            $table->dropIfExists('senderName');
        });
    }
}

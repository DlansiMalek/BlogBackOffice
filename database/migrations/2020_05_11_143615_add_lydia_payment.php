<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLydiaPayment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Config_Congress', function (Blueprint $table) {
            $table->string('lydia_token')->nullable();
            $table->string('lydia_api')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Config_Congress', function (Blueprint $table) {
            $table->dropColumn(['lydia_token']);
            $table->dropColumn(['lydia_api']);
        });
    }
}

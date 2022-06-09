<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDnsPwaToRequestLandingPage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Request_Landing_Page', function (Blueprint $table) {
            $table->tinyInteger('dns_pwa')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Request_Landing_Page', function (Blueprint $table) {
            $table->removeColumn('dns_pwa');
        });
    }
}

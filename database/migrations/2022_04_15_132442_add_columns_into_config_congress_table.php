<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsIntoConfigCongressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Config_Congress', function (Blueprint $table) {
            $table->string('networking_fixe_msg')->nullable()->default(null);
            $table->string('networking_fixe_msg_en')->nullable()->default(null);
            $table->string('networking_libre_msg')->nullable()->default(null);
            $table->string('networking_libre_msg_en')->nullable()->default(null);
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
            $table->removeColumn('networking_fixe_msg');
            $table->removeColumn('networking_fixe_msg_en');
            $table->removeColumn('networking_libre_msg');
            $table->removeColumn('networking_libre_msg_en');
            
        });
    }
}

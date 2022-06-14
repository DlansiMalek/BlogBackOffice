<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeHomeBtnTextConfigLP extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Config_LP', function (Blueprint $table) {
            $table->string('home_btn_text')->default('LOGIN')->nullable()->change();
            $table->string('home_btn_link')->default('/landingpage/{congressId}/login')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Config_LP', function (Blueprint $table) {
            $table->string('home_btn_text')->default('LOGIN')->change();
            $table->string('home_btn_link')->default('/landingpage/{congressId}/login')->change();
        });
    }
}

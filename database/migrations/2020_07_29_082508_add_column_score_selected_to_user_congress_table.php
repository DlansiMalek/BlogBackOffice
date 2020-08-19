<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnScoreSelectedToUserCongressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('User_Congress', function (Blueprint $table) {
            $table->integer('globale_score')->nullable()->default(0)->after('isPresent');
            $table->integer('isSelected')->nullable()->default(0)->after('globale_score');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('User_Congress', function (Blueprint $table) {
            $table->dropColumn(['globale_score','isSelected']);
        });
    }
}

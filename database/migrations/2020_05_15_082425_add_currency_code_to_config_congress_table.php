<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCurrencyCodeToConfigCongressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Config_Congress', function (Blueprint $table) {
            
            $table->string('currency_code')->after('congress_id')->nullable()->default('TND');
            $table->foreign('currency_code')->references('code')->on('Currency')->onDelete('cascade');
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
            $table->dropForeign(['currency_code']);
        });
    }
}

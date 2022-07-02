<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableLpSponsorPack extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('LP_Sponsor_Pack', function (Blueprint $table) {
            $table->increments('lp_sponsor_pack_id');
            $table->longText('description')->nullable()->default(null);
            $table->longText('description_en')->nullable()->default(null);
            $table->longText('description_ar')->nullable()->default(null);
            $table->unsignedInteger('congress_id');
            $table->foreign('congress_id')->references('congress_id')
                ->on('Congress')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('LP_Sponsor_Pack', function (Blueprint $table) {
            $table->dropForeign(['congress_id']);
        });
        Schema::dropIfExists('LP_Sponsor_Pack');
    }
}

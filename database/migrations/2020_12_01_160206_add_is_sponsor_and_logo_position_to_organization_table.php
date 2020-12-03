<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsSponsorAndLogoPositionToOrganizationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Organization', function (Blueprint $table) {
            $table->tinyInteger('isSponsor')->default(0);
            $table->string('logoPosition')->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Organization', function (Blueprint $table) {
            $table->removeColumn('isSponsor');
            $table->removeColumn('logoPosition');
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCongressIdToEtablissement extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Etablissement', function (Blueprint $table) {
            $table->unsignedInteger('congress_id')->nullable()->default(null);
            $table->foreign('congress_id')->references('congress_id')->on('Congress')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Etablissement', function (Blueprint $table) {
            $table->dropForeign('congress_id');
            $table->removeColumn('congress_id');
        });
    }
}

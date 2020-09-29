<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInternalPrivReferenceCongressIdToPrivilegeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Privilege', function (Blueprint $table) {
            $table->unsignedInteger('priv_reference')->nullable()
            ->default(null);
            $table->foreign('priv_reference')->references('privilege_id')
                ->on('Privilege')
                ->onDelete('cascade');
            $table->tinyInteger('internal')->default(0);
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
        Schema::table('Privilege', function (Blueprint $table) {
            $table->dropColumn(['priv_reference','internal','congress_id']);
        }); 
    }
}

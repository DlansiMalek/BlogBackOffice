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
            $table->unsignedInteger('priv_reference')->after('name')
                ->nullable()->default(null);
            $table->foreign('priv_reference')->references('privilege_id')
                ->on('Privilege')
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
            $table->dropColumn(['priv_reference']);
        }); 
    }
}

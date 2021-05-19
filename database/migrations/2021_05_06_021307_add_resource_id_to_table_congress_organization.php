<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddResourceIdToTableCongressOrganization extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('congress_organization', function (Blueprint $table) {
          $table->unsignedInteger("resource_id")->nullable()->default(null);
            $table->foreign("resource_id")->references('resource_id')->on('Resource')
                ->onDelete('cascade');

            $table->tinyInteger('is_sponsor')->default(0)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('congress_organization', function (Blueprint $table) {
            //
        });
    }
}

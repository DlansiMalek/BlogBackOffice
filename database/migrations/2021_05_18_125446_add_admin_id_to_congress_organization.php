<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdminIdToCongressOrganization extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('congress_organization', function (Blueprint $table) {
          $table->unsignedInteger("admin_id")->nullable()->default(null);
            $table->foreign("admin_id")->references('admin_id')->on('admin')
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
        Schema::table('congress_organization', function (Blueprint $table) {
            //
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColmnnBannerToTableCongressOrganization extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Congress_Organization', function (Blueprint $table) {
            $table->string('banner')->nullable()->default(null);
            $table->unsignedInteger("resource_id")->nullable()->default(null);
            $table->foreign("resource_id")->references('resource_id')->on('Resource')
                ->onDelete('cascade');
            $table->tinyInteger('is_sponsor')->default(0)->nullable();
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
        Schema::table('Congress_Organization', function (Blueprint $table) {
            $table->removeColumn("banner");
            $table->removeColumn("is_sponsor");
            $table->dropForeign(['resource_id']);
            $table->dropForeign(['admin_id']);
        });
    }
}

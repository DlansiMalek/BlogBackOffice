<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEmailToOrganization extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Organization', function (Blueprint $table) {
            $table->string('email');
            $table->removeColumn("is_sponsor");
            $table->dropForeign(['resource_id']);
            $table->removeColumn("resource_id");
            $table->dropForeign(['admin_id']);
            $table->removeColumn("admin_id");
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
            $table->removeColumn(['email']);
            $table->tinyInteger('is_sponsor')->default(0)->nullable();
            $table->unsignedInteger("resource_id")->nullable()->default(null);
            $table->foreign("resource_id")->references('resource_id')->on('Resource')
                ->onDelete('cascade');
            $table->unsignedInteger("admin_id")->nullable()->default(null);
            $table->foreign("admin_id")->references('admin_id')->on('Admin')
                ->onDelete('cascade');
        });
    }
}

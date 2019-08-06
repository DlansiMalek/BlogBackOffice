<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePackModuleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Pack_Admin_Module', function (Blueprint $table) {
            $table->increments('pack_admin_module_id');
            $table->unsignedInteger('pack_admin_id');
            $table->unsignedInteger('module_id');
            $table->foreign('pack_admin_id')->references('pack_admin_id')->on('Pack_Admin')->onDelete('cascade');
            $table->foreign('module_id')->references('module_id')->on('Module')->onDelete('cascade');

            $table->softDeletes();
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
        Schema::table('Pack_Admin_Module', function (Blueprint $table) {
            $table->dropForeign(['pack_admin_id']);
            $table->dropForeign(['module_id']);
        });
        Schema::dropIfExists('Pack_Admin_Module');
    }
}

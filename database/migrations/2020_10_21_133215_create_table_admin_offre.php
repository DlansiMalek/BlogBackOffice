<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableAdminOffre extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Admin_Offre', function (Blueprint $table) {
            $table->increments('admin_offre_id');

            $table->unsignedInteger('admin_id');
            $table->foreign('admin_id')->references('admin_id')->on('Admin');

            $table->unsignedInteger('offre_id');
            $table->foreign('offre_id')->references('offre_id')->on('Offre');

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
        Schema::table('Admin_Offre', function (Blueprint $table) {
            $table->dropForeign(['admin_id']);
            $table->dropForeign(['offre_id']);
        });
        Schema::dropIfExists('Admin_Offre');
    }
}

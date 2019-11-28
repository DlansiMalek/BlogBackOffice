<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHistoryPackTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('History_Pack', function (Blueprint $table) {
            $table->increments('history_id');
            $table->tinyInteger('status');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->tinyInteger('nbr_events')->default(0);
            $table->unsignedInteger('pack_admin_id');
            $table->unsignedInteger('admin_id');
            $table->foreign('pack_admin_id')->references('pack_admin_id')->on('Pack_Admin')
                ->onDelete('cascade');
            $table->foreign('admin_id')->references('admin_id')->on('Admin')->onDelete('cascade');
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
        Schema::table('History_Pack', function (Blueprint $table) {
            $table->dropForeign(['pack_admin_id']);
            $table->dropForeign(['admin_id']);
        });
        Schema::dropIfExists('History_Pack');
    }
}

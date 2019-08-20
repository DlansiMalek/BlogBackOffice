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
        Schema::create('History_pack', function (Blueprint $table) {
            $table->increments('history_id');
            $table->string('status');
            $table->unsignedInteger('pack_admin_id');
            $table->unsignedInteger('admin_id');
            $table->foreign('pack_admin_id')->references('pack_admin_id')->on('Pack_Admin')
                ->onDelete('cascade');
            $table->foreign('admin_id')->references('admin_id')->on('Admin') ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    { Schema::table('history_pack', function (Blueprint $table) {
        $table->dropForeign(['pack_id']);
        $table->dropForeign(['admin_id']);
    });
        Schema::dropIfExists('history_pack');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnUserIdCongressIdTracking extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Tracking', function (Blueprint $table) {
            $table->unsignedInteger("user_id");
            $table->foreign("user_id")->references('user_id')->on('User')
                ->onDelete('cascade');

            $table->unsignedInteger("congress_id");
            $table->foreign("congress_id")->references('congress_id')->on('Congress')
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
        //
    }
}

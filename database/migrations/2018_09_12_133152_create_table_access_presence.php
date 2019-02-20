<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableAccessPresence extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Access_Presence', function (Blueprint $table) {
            $table->increments('access_presence_id');

            $table->dateTime('enter_time')->nullable()->default(null);
            $table->dateTime('leave_time')->nullable()->default(null);

            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('user_id')->on('User')
                ->onDelete('cascade');

            $table->integer('access_id')->unsigned();
            $table->foreign('access_id')->references('access_id')->on('Access')
                ->onDelete('cascade');

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
        //
    }
}

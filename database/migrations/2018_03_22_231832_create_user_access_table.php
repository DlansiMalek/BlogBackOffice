<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserAccessTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('User_Access', function (Blueprint $table) {
            $table->increments('user_access_id');


            $table->tinyInteger('isPresent')->unsigned()->default(0);

            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('user_id')->on('User')
                ->onDelete('cascade');

            $table->integer('access_id')->unsigned();
            $table->foreign('access_id')->references('access_id')->on('Access')
                ->onDelete('cascade');
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
        Schema::dropIfExists('user_access');
    }
}

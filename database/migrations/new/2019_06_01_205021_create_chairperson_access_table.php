<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChairPersonAccess extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ChairPerson_Access', function (Blueprint $table) {
            $table->increments('chairperson_Access_id');

            //Access
            $table->integer('access_id')->unsigned();
            $table->foreign('access_id')->references('access_id')->on("Access");

            //User
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('user_id')->on("User");


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
        Schema::dropIfExists('ChairPerson_Access');
    }
}

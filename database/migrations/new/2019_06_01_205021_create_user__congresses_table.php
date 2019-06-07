<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserCongressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('User_Congress', function (Blueprint $table) {
            $table->increments('user_congress_id');

            //Congress
            $table->integer('congress_id')->unsigned();
            $table->foreign('congress_id')->references('congress_id')->on("Congress")
                ->onDelete('cascade');

            //User
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('user_id')->on("User")
                ->onDelete('cascade');

            //Privilege
            $table->integer('privilege_id')->unsigned();
            $table->foreign('privilege_id')->references('privilege_id')->on("Privilege");

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
        Schema::dropIfExists('User_Congress');
    }
}

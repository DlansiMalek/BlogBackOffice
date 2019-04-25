<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserMailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('User_Mail', function (Blueprint $table) {
            $table->increments('user_mail_id');
            $table->integer('user_id')->unsigned();
            $table->integer('mail_id')->unsigned();
            $table->foreign('user_id')->references('user_id')->on('User');
            $table->foreign('mail_id')->references('mail_id')->on('Mail');
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
        Schema::dropIfExists('user_mail');
    }
}

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

            $table->unsignedTinyInteger('status');

            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('user_id')->on('User')->onDelete('cascade');

            $table->unsignedInteger('mail_id');
            $table->foreign('mail_id')->references('mail_id')->on('Mail')->onDelete('cascade');

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
        Schema::dropIfExists('user_mail');
    }
}

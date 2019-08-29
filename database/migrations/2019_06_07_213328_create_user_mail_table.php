<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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

            $table->tinyInteger('status')->default(0);

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
        Schema::table('User_Mail', function (Blueprint $table) {
            $table->dropForeign(['mail_id']);
            $table->dropForeign(['user_id']);
        });
        Schema::dropIfExists('User_Mail');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserMailAdminTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('User_Mail_Admin', function (Blueprint $table) {
            $table->increments('user_mail_admin_id');

            $table->tinyInteger('status')->default(0);

            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('user_id')->on('User')->onDelete('cascade');

            $table->unsignedInteger('mail_admin_id');
            $table->foreign('mail_admin_id')->references('mail_admin_id')->on('Mail_Admin')->onDelete('cascade');

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
        Schema::table('User_Mail_Admin', function (Blueprint $table) {
            $table->dropForeign(['mail_admin_id']);
            $table->dropForeign(['user_id']);
        });
        Schema::dropIfExists('User_Mail_Admin');
    }
}

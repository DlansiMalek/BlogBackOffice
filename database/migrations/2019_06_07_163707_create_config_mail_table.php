<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConfigMailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Config_Mail', function (Blueprint $table) {
            $table->increments('config_mail_id');
            $table->string('username');
            $table->string('password');
            $table->string('mail_name');
            $table->string('mail_address');
            $table->string('driver');
            $table->string('host');
            $table->unsignedInteger('port');
            $table->string('encryption');
            $table->unsignedInteger('congress_id');
            $table->foreign('congress_id')->references('congress_id')->on('Congress');

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
        Schema::table('Config_Mail', function (Blueprint $table) {
            $table->dropForeign(['congress_id']);
        });
        Schema::dropIfExists('Config_Mail');
    }
}

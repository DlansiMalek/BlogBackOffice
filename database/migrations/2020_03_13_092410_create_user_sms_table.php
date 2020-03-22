<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserSmsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('User_SMS', function (Blueprint $table) {
            $table->bigIncrements('user_sms_id');
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('user_id')->on('User')->onDelete('cascade');

            $table->unsignedBigInteger('custom_sms_id');
            $table->foreign('custom_sms_id')->references('custom_sms_id')->on('Custom_SMS')->onDelete('cascade');

            $table->integer('status')->default(0);

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
        Schema::dropIfExists('User_SMS');
    }
}

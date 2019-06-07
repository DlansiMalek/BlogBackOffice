<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('User', function (Blueprint $table) {
            $table->increments('user_id');

            $table->string('first_name');
            $table->string('last_name');
            $table->integer('gender')->nullable();
            $table->string('mobile');

            $table->string('email');
            $table->unsignedTinyInteger('email_verified')->default(0);
            $table->string('verification_code')->nullable();

            $table->string('qr_code');
            $table->string("rfid")->nullable()->default(null);

            $table->integer('country_id')->unsigned()->nullable()->default(null);
            $table->foreign('country_id')->references('country_id')->on('Country')->onDelete('cascade');

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
        Schema::dropIfExists('user');
    }
}

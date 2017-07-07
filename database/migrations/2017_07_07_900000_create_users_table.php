<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('user_id');
            $table->string('first_name');
            $table->string('last_name');
            $table->integer('gender');
            $table->boolean('valide');
            $table->string('postal');
            $table->string('address');
            $table->string('domain');
            $table->string('establishment');
            $table->string('profession');
            $table->string('tel');
            $table->string('mobile');
            $table->string('fax');
            $table->string('email')->unique();
            $table->string('cin')->unique();
            $table->string('validation_code');
            $table->rememberToken();
            $table->timestamps();
            $table->integer('city_id')->unsigned();
            $table->foreign('city_id')->references('city_id')->on('cities')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['city_id']);
        });
        Schema::dropIfExists('users');
    }
}

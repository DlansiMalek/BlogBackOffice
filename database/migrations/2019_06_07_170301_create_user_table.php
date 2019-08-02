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
            $table->string('mobile')->nullable()->default(null);

            $table->string('email');
            $table->unsignedTinyInteger('email_verified')->default(0);
            $table->string('verification_code')->nullable();

            $table->string('qr_code')->nullable()->default(null);
            $table->string("rfid")->nullable()->default(null);

            $table->string('profile_pic')->nullable()->default(null);

            $table->string('country_id')->nullable()->default(null);
            $table->foreign('country_id')->references('alpha3code')->on('Country')->onDelete('set null');

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
        Schema::table('User', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
        });
        Schema::dropIfExists('User');
    }
}

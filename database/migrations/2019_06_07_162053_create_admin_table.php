<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Admin', function (Blueprint $table) {
            $table->increments('admin_id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('mobile');
            $table->string('password');
            $table->string('passwordDecrypt');
            $table->string("rfid")->nullable()->default(null);


            $table->unsignedInteger('privilege_id')->nullable()->default(null);
            $table->foreign('privilege_id')->references('privilege_id')->on('Privilege');

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
        Schema::table('Admin', function (Blueprint $table) {
            $table->dropForeign(['privilege_id']);
        });
        Schema::dropIfExists('Admin');
    }
}

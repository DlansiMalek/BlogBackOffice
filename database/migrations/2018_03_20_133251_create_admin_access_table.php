<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdminAccessTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Admin_Access', function (Blueprint $table) {
            $table->increments('admin_access_id');

            $table->integer('admin_id')->unsigned();
            $table->foreign('admin_id')->references('admin_id')->on('Admin')
                ->onDelete('cascade');

            $table->integer('access_id')->unsigned();
            $table->foreign('access_id')->references('access_id')->on('Access')
                ->onDelete('cascade');

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
        Schema::dropIfExists('Admin_Access');
    }
}
